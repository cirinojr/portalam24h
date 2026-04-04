<?php

require_once __DIR__ . '/../Support/AssetLocator.php';
require_once __DIR__ . '/../Support/LanguageCatalog.php';
require_once __DIR__ . '/../Support/ThemeOptionsRepository.php';
require_once __DIR__ . '/../Support/Localization.php';
require_once __DIR__ . '/../Support/ColorUtils.php';
require_once __DIR__ . '/../Support/helpers.php';

require_once __DIR__ . '/ThemeSetup.php';
require_once __DIR__ . '/Assets.php';

require_once __DIR__ . '/../Performance/CriticalCss.php';
require_once __DIR__ . '/../Performance/HeadStyles.php';
require_once __DIR__ . '/../Performance/Cleanup.php';

require_once __DIR__ . '/../Admin/SettingsSanitizer.php';
require_once __DIR__ . '/../Admin/SettingsRegistrar.php';
require_once __DIR__ . '/../Admin/ThemeSettingsPage.php';
require_once __DIR__ . '/../Admin/HideTitleMetabox.php';

require_once __DIR__ . '/../Front/ThemeCustomCss.php';
require_once __DIR__ . '/../Front/CookieConsentBanner.php';
require_once __DIR__ . '/../Front/AccessibilityPopup.php';
require_once __DIR__ . '/../Front/ThirdPartyScripts.php';

require_once __DIR__ . '/../Typography/TypographySettings.php';
require_once __DIR__ . '/../Typography/FontValidator.php';
require_once __DIR__ . '/../Typography/FontStorageManager.php';
require_once __DIR__ . '/../Typography/FontProviderGoogle.php';
require_once __DIR__ . '/../Typography/FontDownloader.php';
require_once __DIR__ . '/../Typography/FontRegistry.php';
require_once __DIR__ . '/../Typography/FontFaceGenerator.php';
require_once __DIR__ . '/../Typography/FontLoader.php';
require_once __DIR__ . '/../Typography/FontAdminController.php';

require_once __DIR__ . '/../Content/ExcerptManager.php';
require_once __DIR__ . '/../Content/FeaturedImagePriority.php';

class Am24h_Bootstrap
{
    private static ?Am24h_Bootstrap $instance = null;

    private Am24h_ThemeOptionsRepository $options;

    /**
     * @var object[]
     */
    private array $modules = array();

    private function __construct()
    {
        $assets = new Am24h_AssetLocator();
        $this->options = new Am24h_ThemeOptionsRepository();
        $sanitizer = new Am24h_SettingsSanitizer();
        $font_validator = new Am24h_FontValidator();
        $typography_settings = new Am24h_TypographySettings();
        $font_storage = new Am24h_FontStorageManager($font_validator);
        $font_provider = new Am24h_FontProviderGoogle($font_validator);
        $font_downloader = new Am24h_FontDownloader($font_provider, $font_storage, $font_validator);
        $font_registry = new Am24h_FontRegistry($typography_settings, $font_storage, $font_validator);
        $font_face_generator = new Am24h_FontFaceGenerator();

        $this->modules = array(
            new Am24h_ThemeSetup(),
            new Am24h_Assets($assets, $this->options),
            new Am24h_Localization($this->options),
            new Am24h_Cleanup($this->options),
            new Am24h_CriticalCss($assets),
            new Am24h_HeadStyles($assets),
            new Am24h_ThemeCustomCss($this->options),
            new Am24h_CookieConsentBanner($this->options, $assets),
            new Am24h_AccessibilityPopup($this->options, $assets),
            new Am24h_ThirdPartyScripts($this->options, $assets),
            new Am24h_FontLoader($font_registry, $typography_settings, $font_face_generator, $this->options),
            new Am24h_SettingsRegistrar($sanitizer),
            new Am24h_ThemeSettingsPage($this->options, $sanitizer),
            new Am24h_FontAdminController($font_provider, $font_downloader, $font_registry, $typography_settings, $font_validator),
            new Am24h_HideTitleMetabox(),
            new Am24h_ExcerptManager(),
            new Am24h_FeaturedImagePriority(),
        );
    }

    public static function instance(): Am24h_Bootstrap
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public function boot(): void
    {
        foreach ($this->modules as $module) {
            if (method_exists($module, 'register_hooks')) {
                $module->register_hooks();
            }
        }
    }

    public function options(): Am24h_ThemeOptionsRepository
    {
        return $this->options;
    }
}
