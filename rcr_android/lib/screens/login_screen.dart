import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:url_launcher/url_launcher.dart';

import '../api/api_client.dart';
import '../state/session.dart';
import '../theme.dart';
import '../widgets/common.dart';
import 'don_screen.dart';
import 'register_screen.dart';

class LoginScreen extends StatefulWidget {
  const LoginScreen({super.key});
  @override
  State<LoginScreen> createState() => _LoginScreenState();
}

class _LoginScreenState extends State<LoginScreen> {
  final _form = GlobalKey<FormState>();
  final _id = TextEditingController();
  final _mdp = TextEditingController();
  bool _busy = false, _voir = false;
  String? _erreur;

  @override
  void dispose() {
    _id.dispose();
    _mdp.dispose();
    super.dispose();
  }

  Future<void> _connexion() async {
    if (!_form.currentState!.validate()) return;
    setState(() { _busy = true; _erreur = null; });
    try {
      await context.read<Session>().login(_id.text.trim(), _mdp.text);
    } on ApiException catch (e) {
      if (mounted) setState(() => _erreur = e.message);
    } finally {
      if (mounted) setState(() => _busy = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      body: SafeArea(
        child: Center(
          child: SingleChildScrollView(
            padding: const EdgeInsets.all(24),
            child: ConstrainedBox(
              constraints: const BoxConstraints(maxWidth: 440),
              child: Form(
                key: _form,
                child: Column(crossAxisAlignment: CrossAxisAlignment.stretch, children: [
                  Image.asset('assets/logo.png', height: 96),
                  const SizedBox(height: 14),
                  const Text('Rassemblement des Chrétiens Républicains',
                      textAlign: TextAlign.center, style: TextStyle(fontSize: 18, fontWeight: FontWeight.w700, color: Rcr.ink)),
                  const SizedBox(height: 4),
                  const Text('Espace membre', textAlign: TextAlign.center, style: TextStyle(color: Rcr.soft)),
                  const SizedBox(height: 28),
                  if (_erreur != null) ErrorBox(_erreur!),
                  TextFormField(
                    controller: _id,
                    keyboardType: TextInputType.emailAddress,
                    autofillHints: const [AutofillHints.username],
                    textInputAction: TextInputAction.next,
                    decoration: const InputDecoration(labelText: "Code d'adhésion ou e-mail", prefixIcon: Icon(Icons.person_outline)),
                    validator: (v) => (v == null || v.trim().isEmpty) ? 'Champ obligatoire' : null,
                  ),
                  const SizedBox(height: 14),
                  TextFormField(
                    controller: _mdp,
                    obscureText: !_voir,
                    autofillHints: const [AutofillHints.password],
                    onFieldSubmitted: (_) => _connexion(),
                    decoration: InputDecoration(
                      labelText: 'Mot de passe',
                      prefixIcon: const Icon(Icons.lock_outline),
                      suffixIcon: IconButton(icon: Icon(_voir ? Icons.visibility_off : Icons.visibility), onPressed: () => setState(() => _voir = !_voir)),
                    ),
                    validator: (v) => (v == null || v.isEmpty) ? 'Champ obligatoire' : null,
                  ),
                  const SizedBox(height: 20),
                  BusyButton(busy: _busy, label: 'Se connecter', onPressed: _connexion),
                  TextButton(
                    onPressed: () => launchUrl(Uri.parse('https://rcr.cd/index.php?pages=mdp_oublie'), mode: LaunchMode.externalApplication),
                    child: const Text('Mot de passe oublié ?'),
                  ),
                  const Divider(height: 32),
                  OutlinedButton.icon(
                    onPressed: () => Navigator.push(context, MaterialPageRoute(builder: (_) => const RegisterScreen())),
                    icon: const Icon(Icons.how_to_reg_outlined),
                    label: const Text('Adhérer au RCR'),
                  ),
                  const SizedBox(height: 10),
                  OutlinedButton.icon(
                    onPressed: () => Navigator.push(context, MaterialPageRoute(builder: (_) => const DonScreen())),
                    icon: const Icon(Icons.favorite_border),
                    label: const Text('Faire un don'),
                  ),
                ]),
              ),
            ),
          ),
        ),
      ),
    );
  }
}
