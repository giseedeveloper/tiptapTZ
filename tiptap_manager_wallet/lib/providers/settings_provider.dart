import 'package:flutter/material.dart';

import '../l10n/app_strings.dart';
import '../services/storage_service.dart';

class SettingsProvider extends ChangeNotifier {
  SettingsProvider(this._storage);

  final StorageService _storage;

  AppLanguage _language = AppLanguage.english;
  ThemeMode _themeMode = ThemeMode.dark;
  bool _ready = false;

  bool get ready => _ready;
  AppLanguage get language => _language;
  ThemeMode get themeMode => _themeMode;
  AppStrings get strings => AppStrings.of(_language);
  Locale get locale => _language.locale;

  Future<void> bootstrap() async {
    _language = AppLanguage.fromStorageKey(await _storage.loadLanguage());
    _themeMode = await _storage.loadThemeMode();
    _ready = true;
    notifyListeners();
  }

  Future<void> setLanguage(AppLanguage value) async {
    if (_language == value) {
      return;
    }

    _language = value;
    notifyListeners();
    await _storage.saveLanguage(value.storageKey);
  }

  Future<void> setThemeMode(ThemeMode value) async {
    if (_themeMode == value) {
      return;
    }

    _themeMode = value;
    notifyListeners();
    await _storage.saveThemeMode(value);
  }
}
