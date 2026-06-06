import 'package:flutter/material.dart';

import '../core/config.dart';
import '../theme/app_theme.dart';
import '../widgets/tiptap_logo.dart';
import '../widgets/wallet_widgets.dart';

class SplashScreen extends StatefulWidget {
  const SplashScreen({super.key});

  @override
  State<SplashScreen> createState() => _SplashScreenState();
}

class _SplashScreenState extends State<SplashScreen> with TickerProviderStateMixin {
  late final AnimationController _introController;
  late final AnimationController _pulseController;
  late final Animation<double> _logoScale;
  late final Animation<double> _logoOpacity;
  late final Animation<double> _textSlide;
  late final Animation<double> _barProgress;

  @override
  void initState() {
    super.initState();
    _introController = AnimationController(
      vsync: this,
      duration: const Duration(milliseconds: 1400),
    );
    _pulseController = AnimationController(
      vsync: this,
      duration: const Duration(milliseconds: 2200),
    )..repeat();

    _logoScale = Tween<double>(begin: 0.72, end: 1).animate(
      CurvedAnimation(
        parent: _introController,
        curve: const Interval(0, 0.55, curve: Curves.easeOutBack),
      ),
    );
    _logoOpacity = Tween<double>(begin: 0, end: 1).animate(
      CurvedAnimation(
        parent: _introController,
        curve: const Interval(0, 0.35, curve: Curves.easeOut),
      ),
    );
    _textSlide = Tween<double>(begin: 18, end: 0).animate(
      CurvedAnimation(
        parent: _introController,
        curve: const Interval(0.35, 0.75, curve: Curves.easeOutCubic),
      ),
    );
    _barProgress = Tween<double>(begin: 0, end: 1).animate(
      CurvedAnimation(
        parent: _introController,
        curve: const Interval(0.45, 1, curve: Curves.easeInOut),
      ),
    );

    _introController.forward();
  }

  @override
  void dispose() {
    _pulseController.stop();
    _introController.dispose();
    _pulseController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    final isDark = Theme.of(context).brightness == Brightness.dark;

    return PortalBackground(
      child: Scaffold(
        backgroundColor: Colors.transparent,
        body: SafeArea(
          child: Padding(
            padding: const EdgeInsets.symmetric(horizontal: 32),
            child: Column(
              children: [
                const Spacer(flex: 3),
                AnimatedBuilder(
                  animation: Listenable.merge([_introController, _pulseController]),
                  builder: (context, _) {
                    final pulse = 1 + (_pulseController.value * 0.08);

                    return Stack(
                      alignment: Alignment.center,
                      children: [
                        ...List.generate(3, (index) {
                          final delay = index * 0.22;
                          final t = ((_pulseController.value + delay) % 1.0);
                          final scale = 1 + (t * 0.55);
                          final opacity = (1 - t) * (isDark ? 0.22 : 0.14);

                          return Transform.scale(
                            scale: scale,
                            child: Container(
                              width: 132,
                              height: 132,
                              decoration: BoxDecoration(
                                shape: BoxShape.circle,
                                border: Border.all(
                                  color: AppTheme.primary.withValues(alpha: opacity),
                                  width: 1.5,
                                ),
                              ),
                            ),
                          );
                        }),
                        Opacity(
                          opacity: _logoOpacity.value,
                          child: Transform.scale(
                            scale: _logoScale.value * pulse.clamp(1.0, 1.06),
                            child: const TiptapLogo(size: 112),
                          ),
                        ),
                      ],
                    );
                  },
                ),
                const SizedBox(height: 28),
                AnimatedBuilder(
                  animation: _introController,
                  builder: (context, _) {
                    return Transform.translate(
                      offset: Offset(0, _textSlide.value),
                      child: Opacity(
                        opacity: _barProgress.value.clamp(0.0, 1.0),
                        child: Column(
                          children: [
                            Text(
                              AppConfig.appName,
                              textAlign: TextAlign.center,
                              style: Theme.of(context).textTheme.headlineSmall?.copyWith(
                                    fontWeight: FontWeight.w900,
                                    letterSpacing: -0.4,
                                  ),
                            ),
                            const SizedBox(height: 8),
                            Text(
                              'Manager wallet',
                              style: TextStyle(
                                color: AppTheme.textMutedOf(context),
                                fontSize: 14,
                                fontWeight: FontWeight.w600,
                                letterSpacing: 0.4,
                              ),
                            ),
                          ],
                        ),
                      ),
                    );
                  },
                ),
                const Spacer(flex: 2),
                AnimatedBuilder(
                  animation: _barProgress,
                  builder: (context, _) {
                    return Column(
                      children: [
                        ClipRRect(
                          borderRadius: BorderRadius.circular(999),
                          child: LinearProgressIndicator(
                            value: _barProgress.value,
                            minHeight: 4,
                            backgroundColor: AppTheme.glassBorderOf(context),
                            color: AppTheme.primary,
                          ),
                        ),
                        const SizedBox(height: 12),
                        Text(
                          'Securing your session…',
                          style: TextStyle(
                            color: AppTheme.textMutedOf(context),
                            fontSize: 12,
                            fontWeight: FontWeight.w500,
                          ),
                        ),
                        const SizedBox(height: 24),
                      ],
                    );
                  },
                ),
              ],
            ),
          ),
        ),
      ),
    );
  }
}
