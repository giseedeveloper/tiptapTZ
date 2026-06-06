import 'package:flutter/material.dart';

import '../widgets/app_icons.dart';

enum AppLanguage {
  english,
  swahili,
  englishSouthAfrica;

  String get storageKey => name;

  static AppLanguage fromStorageKey(String? key) {
    return AppLanguage.values.firstWhere(
      (l) => l.storageKey == key,
      orElse: () => AppLanguage.english,
    );
  }

  Locale get locale {
    switch (this) {
      case AppLanguage.swahili:
        return const Locale('sw');
      case AppLanguage.englishSouthAfrica:
        return const Locale('en', 'ZA');
      case AppLanguage.english:
        return const Locale('en');
    }
  }

  String label(AppStrings s) {
    switch (this) {
      case AppLanguage.english:
        return s.langEnglish;
      case AppLanguage.swahili:
        return s.langSwahili;
      case AppLanguage.englishSouthAfrica:
        return s.langEnglishSa;
    }
  }
}

class AppStrings {
  const AppStrings(this.language);

  final AppLanguage language;

  static AppStrings of(AppLanguage language) => AppStrings(language);

  String _t({required String en, required String sw, String? enZa}) {
    switch (language) {
      case AppLanguage.swahili:
        return sw;
      case AppLanguage.englishSouthAfrica:
        return enZa ?? en;
      case AppLanguage.english:
        return en;
    }
  }

  String get appName => _t(en: 'TIPTAP Wallet', sw: 'TIPTAP Mkoba');

  String get navWallet => _t(en: 'Wallet', sw: 'Mkoba');
  String get navActivity => _t(en: 'Activity', sw: 'Shughuli');
  String get navPayout => _t(en: 'Payout', sw: 'Malipo');
  String get navSettings => _t(en: 'Settings', sw: 'Mipangilio');

  String hi(String name) => _t(
        en: 'Hi, $name',
        sw: 'Habari, $name',
        enZa: 'Hi, $name',
      );

  String get venueWallet => _t(
        en: 'Your venue wallet',
        sw: 'Mkoba wa biashara yako',
        enZa: 'Your venue wallet',
      );

  String get settingsTitle => _t(en: 'Settings', sw: 'Mipangilio');
  String get settingsSubtitle => _t(
        en: 'Account, restaurant & app preferences',
        sw: 'Akaunti, biashara na mapendeleo ya app',
        enZa: 'Account, restaurant & app preferences',
      );

  String get restaurantSection => _t(en: 'Restaurant', sw: 'Biashara');
  String get managerSection => _t(en: 'Manager account', sw: 'Akaunti ya meneja');
  String get preferencesSection => _t(en: 'Preferences', sw: 'Mapendeleo');
  String get aboutSection => _t(en: 'About', sw: 'Kuhusu app');

  String get restaurantName => _t(en: 'Venue name', sw: 'Jina la biashara');
  String get availableBalance => _t(en: 'Available balance', sw: 'Salio linalopatikana');
  String get platformFee => _t(en: 'Platform fee', sw: 'Ada ya jukwaa');
  String get currency => _t(en: 'Currency', sw: 'Sarafu');
  String get email => _t(en: 'Email', sw: 'Barua pepe');
  String get role => _t(en: 'Role', sw: 'Wajibu');
  String get managerRole => _t(en: 'Manager', sw: 'Meneja');

  String get languageLabel => _t(en: 'Language', sw: 'Lugha');
  String get themeLabel => _t(en: 'Theme', sw: 'Muonekano');
  String get themeDark => _t(en: 'Dark', sw: 'Giza');
  String get themeLight => _t(en: 'Light', sw: 'Mwanga');

  String get langEnglish => 'English';
  String get langSwahili => 'Kiswahili';
  String get langEnglishSa => 'English (South Africa)';

  String get marketRegion => _t(en: 'Market', sw: 'Soko');
  String get marketTz => _t(en: 'Tanzania', sw: 'Tanzania');
  String get marketSa => _t(en: 'South Africa', sw: 'Afrika Kusini');

  String get logout => _t(en: 'Sign out', sw: 'Toka');
  String get logoutConfirmTitle => _t(en: 'Sign out?', sw: 'Unataka kutoka?');
  String get logoutConfirmBody => _t(
        en: 'You will need to sign in again to access your wallet.',
        sw: 'Utahitaji kuingia tena kufikia mkoba wako.',
      );
  String get cancel => _t(en: 'Cancel', sw: 'Ghairi');
  String get confirm => _t(en: 'Sign out', sw: 'Toka');

  String get overview => _t(en: 'Overview', sw: 'Muhtasari');
  String get recentPayments => _t(en: 'Recent payments', sw: 'Malipo ya hivi karibuni');
  String get seeAll => _t(en: 'See all', sw: 'Angalia zote');
  String get withdrawFunds => _t(en: 'Withdraw funds', sw: 'Toa pesa');

  String get payoutTitle => _t(en: 'Payout', sw: 'Malipo');
  String get payoutSubtitle => _t(
        en: 'Where we send your withdrawal money',
        sw: 'Mahali tunapotuma pesa zako',
      );

  String get activityTitle => _t(en: 'Activity', sw: 'Shughuli');
  String get tabPayments => _t(en: 'Payments', sw: 'Malipo');
  String get tabWithdrawals => _t(en: 'Withdrawals', sw: 'Utoaji');

  String get signIn => _t(en: 'Sign in', sw: 'Ingia');
  String get signInSubtitle => _t(
        en: 'Manager wallet — view balance, track payments, and request withdrawals.',
        sw: 'Mkoba wa meneja — angalia salio, malipo, na omba utoaji.',
        enZa: 'Manager wallet — view balance, track payments, and request withdrawals.',
      );

  String labelForTab(WalletNavTab tab) {
    switch (tab) {
      case WalletNavTab.wallet:
        return navWallet;
      case WalletNavTab.activity:
        return navActivity;
      case WalletNavTab.payout:
        return navPayout;
      case WalletNavTab.settings:
        return navSettings;
    }
  }
}
