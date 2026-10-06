import 'package:flutter/material.dart';
import 'package:webview_flutter/webview_flutter.dart';

/// Page de paiement par carte FlexPay dans l'application.
/// Se ferme dès que le serveur renvoie vers success.php / failed.php / loading.php (le statut est ensuite vérifié par l'API).
class CardScreen extends StatefulWidget {
  final String url;
  const CardScreen({super.key, required this.url});
  @override
  State<CardScreen> createState() => _CardScreenState();
}

class _CardScreenState extends State<CardScreen> {
  late final WebViewController _c;
  bool _charge = true, _ferme = false;

  @override
  void initState() {
    super.initState();
    _c = WebViewController()
      ..setJavaScriptMode(JavaScriptMode.unrestricted)
      ..setNavigationDelegate(NavigationDelegate(
        onPageStarted: (url) {
          if (RegExp(r'/adhere/(success|failed|loading)\.php').hasMatch(url)) {
            _terminer();
          }
        },
        onPageFinished: (_) {
          if (mounted) setState(() => _charge = false);
        },
      ))
      ..loadRequest(Uri.parse(widget.url));
  }

  void _terminer() {
    if (_ferme || !mounted) return;
    _ferme = true;
    Navigator.of(context).pop(true);
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Paiement sécurisé'),
        leading: IconButton(icon: const Icon(Icons.close), onPressed: _terminer),
      ),
      body: Stack(children: [
        WebViewWidget(controller: _c),
        if (_charge) const LinearProgressIndicator(),
      ]),
    );
  }
}
