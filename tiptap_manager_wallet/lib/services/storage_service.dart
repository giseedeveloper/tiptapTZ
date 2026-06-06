import 'dart:convert';

import 'package:flutter/material.dart';
import 'package:shared_preferences/shared_preferences.dart';

import '../models/wallet_models.dart';

class StorageService {
  static const _sessionKey = 'manager_wallet_session';
  static const _languageKey = 'manager_wallet_language';
  static const _themeKey = 'manager_wallet_theme';

  Future<void> saveSession(AuthSession session) async {
    final prefs = await SharedPreferences.getInstance();
    await prefs.setString(_sessionKey, jsonEncode(session.toJson()));
  }

  Future<AuthSession?> loadSession() async {
    final prefs = await SharedPreferences.getInstance();
    final raw = prefs.getString(_sessionKey);
    if (raw == null) return null;

    return AuthSession.fromJson(jsonDecode(raw) as Map<String, dynamic>);
  }

  Future<void> clearSession() async {
    final prefs = await SharedPreferences.getInstance();
    await prefs.remove(_sessionKey);
  }

  Future<void> saveLanguage(String code) async {
    final prefs = await SharedPreferences.getInstance();
    await prefs.setString(_languageKey, code);
  }

  Future<String?> loadLanguage() async {
    final prefs = await SharedPreferences.getInstance();
    return prefs.getString(_languageKey);
  }

  Future<void> saveThemeMode(ThemeMode mode) async {
    final prefs = await SharedPreferences.getInstance();
    await prefs.setString(_themeKey, mode.name);
  }

  Future<ThemeMode> loadThemeMode() async {
    final prefs = await SharedPreferences.getInstance();
    final raw = prefs.getString(_themeKey);
    return ThemeMode.values.firstWhere(
      (m) => m.name == raw,
      orElse: () => ThemeMode.dark,
    );
  }
}
