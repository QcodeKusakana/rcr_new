import 'package:flutter/material.dart';
import 'package:provider/provider.dart';

import '../api/api_client.dart';
import '../state/session.dart';
import '../widgets/common.dart';

class PasswordScreen extends StatefulWidget {
  const PasswordScreen({super.key});
  @override
  State<PasswordScreen> createState() => _PasswordScreenState();
}

class _PasswordScreenState extends State<PasswordScreen> {
  final _form = GlobalKey<FormState>();
  final _a = TextEditingController(), _n = TextEditingController(), _c = TextEditingController();
  bool _busy = false, _voir = false;
  String? _erreur;

  @override
  void dispose() {
    _a.dispose();
    _n.dispose();
    _c.dispose();
    super.dispose();
  }

  Future<void> _envoyer() async {
    if (!_form.currentState!.validate()) return;
    setState(() { _busy = true; _erreur = null; });
    try {
      await context.read<Session>().api.post('/me/mot-de-passe', {'actuel': _a.text, 'nouveau': _n.text});
      if (!mounted) return;
      toast(context, 'Mot de passe modifié.');
      Navigator.pop(context);
    } on ApiException catch (e) {
      if (mounted) setState(() => _erreur = e.message);
    } finally {
      if (mounted) setState(() => _busy = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('Mot de passe')),
      body: Form(
        key: _form,
        child: ListView(padding: const EdgeInsets.all(16), children: [
          if (_erreur != null) ErrorBox(_erreur!),
          TextFormField(controller: _a, obscureText: !_voir, decoration: const InputDecoration(labelText: 'Mot de passe actuel'), validator: (v) => (v ?? '').isEmpty ? 'Champ obligatoire' : null),
          const SizedBox(height: 12),
          TextFormField(
            controller: _n, obscureText: !_voir,
            decoration: InputDecoration(labelText: 'Nouveau mot de passe (8 caractères minimum)', suffixIcon: IconButton(icon: Icon(_voir ? Icons.visibility_off : Icons.visibility), onPressed: () => setState(() => _voir = !_voir))),
            validator: (v) => (v ?? '').length < 8 ? '8 caractères minimum' : null,
          ),
          const SizedBox(height: 12),
          TextFormField(controller: _c, obscureText: !_voir, decoration: const InputDecoration(labelText: 'Confirmer le nouveau mot de passe'), validator: (v) => v != _n.text ? 'Les mots de passe ne correspondent pas' : null),
          const SizedBox(height: 22),
          BusyButton(busy: _busy, label: 'Enregistrer', onPressed: _envoyer),
        ]),
      ),
    );
  }
}
