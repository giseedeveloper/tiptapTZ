class AppConfig {
  /// Laravel API root. Override per market at build time:
  /// TZ: --dart-define=API_BASE_URL=https://tiptapafrica.co.tz/api
  /// SA: --dart-define=API_BASE_URL=https://tiptapafrica.co.za/api
  /// Local: --dart-define=API_BASE_URL=http://127.0.0.1:8001/api
  static const String apiBaseUrl = String.fromEnvironment(
    'API_BASE_URL',
    defaultValue: 'https://tiptapafrica.co.tz/api',
  );

  /// Web app origin (OAuth). Derived from API URL when not set.
  static String get webBaseUrl {
    const override = String.fromEnvironment('WEB_BASE_URL');
    if (override.isNotEmpty) return override;
    if (apiBaseUrl.endsWith('/api')) {
      return apiBaseUrl.substring(0, apiBaseUrl.length - 4);
    }
    return apiBaseUrl;
  }

  static const String appName = 'TIPTAP Wallet';
}
