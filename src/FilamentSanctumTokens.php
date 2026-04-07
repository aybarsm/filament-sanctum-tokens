<?php

declare(strict_types=1);

namespace Aybarsm\Filament\SanctumTokens;
use Filament\Panel;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Env;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Fluent;
use Illuminate\Support\Str;
use Laravel\Sanctum\Contracts\HasApiTokens as HasApiTokensContract;
use TomatoPHP\FilamentUsers\FilamentUsersPlugin;
use function Illuminate\Filesystem\join_paths;

final class FilamentSanctumTokens implements namespace\Contracts\FilamentSanctumTokensContract
{
    protected static Fluent $data;
    
    public function __construct()
    {
        $modelsInclude = config('filament-sanctum-tokens.models.include', []);
        $modelsExclude = config('filament-sanctum-tokens.models.exclude', []);
        self::include(...$modelsInclude);
        self::exclude(...$modelsExclude);
    }

    public function getModelClasses(): array
    {
        if (self::getData()->has('discovered')) {
            return self::getData()->get('discovered', []);
        }

        $cache = $this->getCache();
        if (!array_key_exists('discovered', $cache)){
            $includeClasses = self::getData()->get('include.class', []);
            $excludeClasses = self::getData()->get('exclude.class', []);
            $includePaths = self::getData()->get('include.path', []);
            $excludePaths = self::getData()->get('exclude.path', []);
            if (count($includeClasses) > 0 || count($includePaths) > 0) {
                $vendorDir = Env::get('COMPOSER_VENDOR_DIR', base_path('vendor'));
                $classmapPath = join_paths($vendorDir, 'composer', 'autoload_classmap.php');
                $classmap = include $classmapPath;
                foreach($classmap as $class => $path) {
                    if (in_array($class, $includeClasses, true)) {
                        $cache['discovered'][] = $class;
                    }elseif(Str::startsWith(dirname($path), $includePaths) && !Str::startsWith(dirname($path), $excludePaths)){
                        $cache['discovered'][] = $class;
                    }
                }
                $cache['discovered'] = array_filter(
                    array_unique($cache['discovered']),
                    static fn ($class) => !in_array($class, $excludeClasses, true) && self::isClassEligible($class)
                );
            }
        }

        self::getData()->set('discovered', $cache['discovered']);

        if ($this->cacheEnabled()) $this->putCache($cache);

        return $cache['discovered'];
    }

    public static function isClassEligible(string $class): bool
    {
        if (!class_exists($class)) return false;
        if (!is_subclass_of($class, Model::class)) return false;
        if (!is_subclass_of($class, AuthenticatableContract::class)) return false;
        return is_subclass_of($class, HasApiTokensContract::class);
    }

    public static function include(...$pathsOrClasses): void
    {
        foreach($pathsOrClasses as $item) {
            self::addInclude($item);
        }
    }

    public static function exclude(...$pathsOrClasses): void
    {
        foreach($pathsOrClasses as $item) {
            self::addExclude($item);
        }
    }
    protected static function addInclude(string $item): void
    {
        $isClass = class_exists($item);
        $isPath = file_exists($item) && is_dir($item) && is_readable($item);
        $item = $isPath ? realpath($item) : $item;

        throw_if(
            !$isClass && !$isPath,
            namespace\Exceptions\FilamentSanctumTokensException::class,
            sprintf('Discovery include item [%s] not a class or readable directory path.', $item)
        );

        $key = 'include.' . ($isClass ? 'class' : 'path');
        if (in_array($item, self::getData()->get($key, []), true)) return;
        self::dataPush($key, $item);
    }

    protected static function addExclude(string $item): void
    {
        $isClass = class_exists($item);
        $isPath = file_exists($item) && is_dir($item);
        $item = $isPath ? realpath($item) : $item;

        throw_if(
            !$isClass && !$isPath,
            namespace\Exceptions\FilamentSanctumTokensException::class,
            sprintf('Discovery exclusion item [%s] not a class or directory path.', $item)
        );

        $key = 'exclude.' . ($isClass ? 'class' : 'path');
        if (in_array($item, self::getData()->get($key, []), true)) return;
        self::dataPush($key, $item);
    }

    public function cacheEnabled(): bool
    {
        $config = config('filament-sanctum-tokens.cache.enabled');
        return in_array($config, [true, false], true) ? $config : app()->isProduction();
    }

    public function getCacheKey(): string
    {
        $config = config('filament-sanctum-tokens.cache.key');
        return filled($config) ? $config : 'filament-sanctum-tokens';
    }

    public function getCacheStore(): \Illuminate\Contracts\Cache\Repository
    {
        $config = config('filament-sanctum-tokens.cache.store');
        return filled($config) ? Cache::store($config) : Cache::store(Cache::getStore());
    }

    public function getCache(): array
    {
        return $this->cacheEnabled() ? $this->getCacheStore()->get($this->getCacheKey(), []) : [];
    }
    protected function putCache(array $context): bool
    {
        return $this->getCacheStore()->forever($this->getCacheKey(), $context);
    }

    protected static function getData(): Fluent
    {
        if (!isset(self::$data)) self::$data = new Fluent();
        return self::$data;
    }

    protected static function dataPush(string $key, ...$values): void
    {
        if (count($values) === 0) return;
        $current = self::getData()->get($key, []);
        array_push($current, ...$values);
        self::getData()->set($key, $current);
    }

    protected static function dataUnshift(string $key, ...$values): void
    {
        if (count($values) === 0) return;
        $current = self::getData()->get($key, []);
        array_unshift($current, ...$values);
        self::getData()->set($key, $current);
    }

    public static function getTokenModel(): string
    {
        return \Laravel\Sanctum\Sanctum::personalAccessTokenModel();
    }

    public static function getTokenModelObject(): Model
    {
        return self::getTokenModel()::getModel();
    }
}
