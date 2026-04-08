<?php

declare(strict_types=1);

namespace Aybarsm\Filament\SanctumTokens;
use Filament\Panel;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Carbon;
use Illuminate\Support\Env;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Fluent;
use Illuminate\Support\Str;
use Laravel\Sanctum\Contracts\HasApiTokens as HasApiTokensContract;
use TomatoPHP\FilamentUsers\FilamentUsersPlugin;
use function Illuminate\Filesystem\join_paths;
use Illuminate\Database\Eloquent\Builder;
use Laravel\Sanctum\Sanctum;

final class FilamentSanctumTokens implements namespace\Contracts\FilamentSanctumTokensContract
{
    protected static Fluent $data;

    public static function getFilamentPluginClass(): string
    {
        return namespace\FilamentSanctumTokensPlugin::class;
    }
    public function getDiscoveredModels(): array
    {
        if (self::getData()->has('discovered')) {
            return self::getData()->get('discovered', []);
        }

        $cache = $this->getCache() ?? [];
        if (!array_key_exists('discovered', $cache)) {
            $cache['discovered'] = [];
            $discovery = $this->getModelDiscovery();
            $classes = array_flip($discovery['include']['class']);
            $files = array_flip($discovery['include']['file']);
            $dirs = $discovery['include']['dir'];

            if (count($classes) === 0 && count($files) === 0 && count($dirs) === 0) {
                self::getData()->set('discovered', $cache['discovered']);
                return $cache['discovered'];
            }

            $excludesRaw = $discovery['exclude'];
            $excludes = array_flip($excludesRaw);

            $vendorDir = Env::get('COMPOSER_VENDOR_DIR', base_path('vendor'));
            $classmapPath = join_paths($vendorDir, 'composer', 'autoload_classmap.php');
            $classmap = include $classmapPath;
            foreach ($classmap as $class => $path) {
                if (isset($cache['discovered'][$class])) {
                    continue;
                } elseif (!isset($classes[$class]) && !isset($files[$path]) && !Str::startsWith(dirname($path), $dirs)) {
                    continue;
                } elseif (isset($excludes[$class]) || isset($excludes[$path]) || Str::startsWith(dirname($path), $excludesRaw)) {
                    continue;
                } elseif (!self::isClassEligible($class)) {
                    continue;
                }

                $cache['discovered'][$class] = null;
            }

            $cache['discovered'] = array_keys($cache['discovered']);
        }

        sort($cache['discovered']);

        self::getData()->set('discovered', $cache['discovered']);
        $this->putCache($cache);

        return $cache['discovered'];
    }

    public static function getSanctumExpiration(): ?float
    {
        $expiration = (float) config('sanctum.expiration');
        return $expiration > 0 ? $expiration : null;
    }
    public static function getTokenDefaultExpiresAt(): ?\DateTimeInterface
    {
        $expiration = self::getSanctumExpiration();
        return $expiration ? Carbon::now()->addMinutes($expiration) : null;
    }

    protected function getCache(): ?array
    {
        return $this->getCacheStore()?->get($this->getCacheKey(), []);
    }

    protected static function isClassEligible(string $class): bool
    {
        if (!class_exists($class)) return false;
        if (!is_subclass_of($class, Model::class)) return false;
        if (!is_subclass_of($class, AuthenticatableContract::class)) return false;
        return is_subclass_of($class, HasApiTokensContract::class);
    }
    protected function getCacheStore(): ?\Illuminate\Contracts\Cache\Repository
    {
        $isEnabled = config('filament-sanctum-tokens.cache.enabled');
        $isEnabled = in_array($isEnabled, [true, false], true) ? $isEnabled : app()->isProduction();
        if (!$isEnabled) return null;

        $store = config('filament-sanctum-tokens.cache.store');
        $store = filled($store) ? $store : Cache::getStore();
        return Cache::store($store);
    }

    protected function getModelDiscovery(): array
    {
        if (self::getData()->has('discovery')) {
            return self::getData()->get('discovery', []);
        }

        $ret = self::getDiscoveryTemplate();
        $includes = config('filament-sanctum-tokens.models.include', []);
        $excludes = config('filament-sanctum-tokens.models.exclude', []);

        if (count($includes) === 0 && count($excludes) === 0){
            self::getData()->set('discovery', $ret);
            return $ret;
        }

        $ret['exclude'] = array_unique(array_map(
            static fn ($item) => !class_exists($item) && file_exists($item) ? realpath($item) : $item,
            $excludes,
        ));

        foreach ($includes as $item) {
            $isClass = class_exists($item);
            self::validateDiscoveryItem($item, $isClass);
            $item = $isClass ? $item : realpath($item);
            $target = $isClass ? 'class' : (is_dir($item) ? 'dir' : 'file');
            if (in_array($item, $ret['include'][$target], true)) continue;
            $ret['include'][$target][] = $item;
        }

        self::getData()->set('discovery', $ret);
        return $ret;
    }

    protected function getCacheKey(): string
    {
        return self::config('cache.key', 'filament-sanctum-tokens');
    }

    protected function config(string $key, mixed $default = null): mixed
    {
        $key = str($key)->split('#\.#', -1, PREG_SPLIT_NO_EMPTY)->toArray();
        if ($key[0] !== 'filament-sanctum-tokens') {
            array_unshift($key, 'filament-sanctum-tokens');
        }
        $key = implode('.', $key);
        $config = config($key);
        return filled($config) ? $config : $default;
    }
    protected function putCache(array $context): void
    {
        $this->getCacheStore()?->forever($this->getCacheKey(), $context);
    }
    protected static function getData(): Fluent
    {
        if (!isset(self::$data)) self::$data = new Fluent();
        return self::$data;
    }

    protected static function getDiscoveryTemplate(): array
    {
        return ['include' => ['class' => [], 'file' => [], 'dir' => []], 'exclude' => []];
    }

    protected static function throw_if(mixed $condition, mixed $message): void
    {
        $condition = value($condition);
        throw_if(
            $condition,
            namespace\Exceptions\FilamentSanctumTokensException::class,
            value($message, $condition)
        );
    }

    protected static function validateDiscoveryItem(string $item, bool $isClass): void
    {
        self::throw_if(
            !$isClass && !file_exists($item),
            sprintf('Discovery include item [%s] is not a class or an existing path.', $item)
        );
        self::throw_if(
            !$isClass && !is_readable($item),
            sprintf('Discovery include path [%s] is not a readable path.', $item)
        );
    }
}
