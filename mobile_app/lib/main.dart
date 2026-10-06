import 'package:flutter/material.dart';
import 'package:flutter_localizations/flutter_localizations.dart';
import 'package:intl/date_symbol_data_local.dart';
import 'package:provider/provider.dart';

import 'screens/home_screen.dart';
import 'screens/login_screen.dart';
import 'state/session.dart';
import 'theme.dart';

Future<void> main() async {
  WidgetsFlutterBinding.ensureInitialized();
  await initializeDateFormatting('fr_FR');
  runApp(ChangeNotifierProvider(create: (_) => Session()..init(), child: const RcrApp()));
}

class RcrApp extends StatelessWidget {
  const RcrApp({super.key});

  @override
  Widget build(BuildContext context) {
    return MaterialApp(
      title: 'RCR',
      debugShowCheckedModeBanner: false,
      theme: Rcr.theme(),
      locale: const Locale('fr', 'FR'),
      supportedLocales: const [Locale('fr', 'FR')],
      localizationsDelegates: const [
        GlobalMaterialLocalizations.delegate,
        GlobalWidgetsLocalizations.delegate,
        GlobalCupertinoLocalizations.delegate,
      ],
      home: const _Racine(),
    );
  }
}

/// Aiguillage selon l'état de la session.
class _Racine extends StatelessWidget {
  const _Racine();

  @override
  Widget build(BuildContext context) {
    final s = context.watch<Session>();
    switch (s.status) {
      case AuthStatus.loading:
        return const Scaffold(body: Center(child: CircularProgressIndicator()));
      case AuthStatus.loggedIn:
        return const HomeScreen();
      case AuthStatus.offline:
        return Scaffold(
          body: Center(
            child: Padding(
              padding: const EdgeInsets.all(28),
              child: Column(mainAxisSize: MainAxisSize.min, children: [
                const Icon(Icons.wifi_off, size: 56, color: Rcr.soft),
                const SizedBox(height: 14),
                const Text('Connexion impossible', style: TextStyle(fontSize: 20, fontWeight: FontWeight.w700)),
                const SizedBox(height: 8),
                const Text('Vérifiez votre accès Internet puis réessayez.', textAlign: TextAlign.center),
                const SizedBox(height: 20),
                FilledButton(onPressed: () => context.read<Session>().init(), child: const Text('Réessayer')),
              ]),
            ),
          ),
        );
      case AuthStatus.loggedOut:
        return const LoginScreen();
    }
  }
}
