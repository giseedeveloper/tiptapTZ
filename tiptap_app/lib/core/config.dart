class AppConfig {
  /// Laravel API root. Override per market at build time:
  /// SA: --dart-define=API_BASE_URL=https://tiptapafrica.co.za/api
  /// TZ: --dart-define=API_BASE_URL=https://tiptapafrica.co.tz/api
  /// Local: --dart-define=API_BASE_URL=http://127.0.0.1:8000/api
  static const String baseUrl = String.fromEnvironment(
    'API_BASE_URL',
    defaultValue: 'https://tiptapafrica.co.tz/api',
  );

  /// Web app origin (OAuth + marketing). Derived from API URL when not set.
  static String get webBaseUrl {
    const override = String.fromEnvironment('WEB_BASE_URL');
    if (override.isNotEmpty) return override;
    if (baseUrl.endsWith('/api')) {
      return baseUrl.substring(0, baseUrl.length - 4);
    }
    return baseUrl;
  }

  static const int statsPollIntervalSeconds = 30;
}
