import 'dart:io';
import 'dart:typed_data';

import 'package:flutter/material.dart';
import 'package:intl/intl.dart';
import 'package:open_filex/open_filex.dart';
import 'package:path_provider/path_provider.dart';

import '../api/api_client.dart';
import '../theme.dart';

String money(num v, [String devise = 'USD']) {
  final f = NumberFormat.decimalPattern('fr_FR');
  final s = v == v.roundToDouble() ? f.format(v) : NumberFormat('#,##0.00', 'fr_FR').format(v);
  return devise == 'USD' ? '$s \$' : '$s $devise';
}

String dateFr(String? iso) {
  if (iso == null || iso.isEmpty) return '—';
  final d = DateTime.tryParse(iso.replaceFirst(' ', 'T'));
  return d == null ? iso : DateFormat('d MMMM yyyy', 'fr_FR').format(d);
}

void toast(BuildContext c, String msg, {bool error = false}) {
  ScaffoldMessenger.of(c)
    ..hideCurrentSnackBar()
    ..showSnackBar(SnackBar(content: Text(msg), backgroundColor: error ? Rcr.red : Rcr.ink, behavior: SnackBarBehavior.floating));
}

/// Pastille de statut (paiement, don, membre).
class StatusChip extends StatelessWidget {
  final String statut;
  const StatusChip(this.statut, {super.key});

  static const _map = <String, List<dynamic>>{
    'paid': ['Payé', Rcr.green],
    'actif': ['Actif', Rcr.green],
    'pending': ['En attente', Rcr.gold],
    'processing': ['En cours', Rcr.gold],
    'en_attente': ['En attente de paiement', Rcr.gold],
    'expired': ['Expiré', Rcr.soft],
    'expire': ['Expiré', Rcr.red],
    'failed': ['Échoué', Rcr.red],
    'cancelled': ['Annulé', Rcr.soft],
    'suspendu': ['Suspendu', Rcr.red],
  };

  @override
  Widget build(BuildContext context) {
    final e = _map[statut] ?? [statut, Rcr.soft];
    final Color c = e[1] as Color;
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
      decoration: BoxDecoration(color: c.withValues(alpha: 0.12), borderRadius: BorderRadius.circular(99)),
      child: Text(e[0] as String, style: TextStyle(color: c, fontSize: 12, fontWeight: FontWeight.w700)),
    );
  }
}

class SectionTitle extends StatelessWidget {
  final String text;
  const SectionTitle(this.text, {super.key});
  @override
  Widget build(BuildContext context) => Padding(
        padding: const EdgeInsets.fromLTRB(2, 18, 2, 8),
        child: Text(text, style: const TextStyle(fontSize: 16, fontWeight: FontWeight.w700, color: Rcr.ink)),
      );
}

class ErrorBox extends StatelessWidget {
  final String message;
  const ErrorBox(this.message, {super.key});
  @override
  Widget build(BuildContext context) => Container(
        width: double.infinity,
        margin: const EdgeInsets.only(bottom: 14),
        padding: const EdgeInsets.all(12),
        decoration: BoxDecoration(color: Rcr.red.withValues(alpha: 0.08), border: Border.all(color: Rcr.red), borderRadius: BorderRadius.circular(10)),
        child: Text(message, style: const TextStyle(color: Rcr.red)),
      );
}

/// Enregistre un PDF reçu de l'API dans le dossier temporaire puis l'ouvre avec le lecteur du téléphone.
Future<void> ouvrirPdf(BuildContext context, Future<Uint8List> Function() charger, String nom) async {
  try {
    final bytes = await charger();
    final dir = await getTemporaryDirectory();
    final f = File('${dir.path}/$nom.pdf');
    await f.writeAsBytes(bytes, flush: true);
    final r = await OpenFilex.open(f.path);
    if (r.type != ResultType.done && context.mounted) {
      toast(context, "Aucune application pour ouvrir le PDF n'est installée.", error: true);
    }
  } on ApiException catch (e) {
    if (context.mounted) toast(context, e.message, error: true);
  } catch (_) {
    if (context.mounted) toast(context, "Impossible d'ouvrir le document.", error: true);
  }
}

/// Bouton plein avec indicateur de chargement (évite les doubles envois).
class BusyButton extends StatelessWidget {
  final bool busy;
  final String label;
  final VoidCallback? onPressed;
  final IconData? icon;
  const BusyButton({super.key, required this.busy, required this.label, required this.onPressed, this.icon});
  @override
  Widget build(BuildContext context) => FilledButton(
        onPressed: busy ? null : onPressed,
        child: busy
            ? const SizedBox(height: 22, width: 22, child: CircularProgressIndicator(strokeWidth: 2.5, color: Colors.white))
            : Row(mainAxisAlignment: MainAxisAlignment.center, children: [
                if (icon != null) ...[Icon(icon, size: 20), const SizedBox(width: 8)],
                Text(label),
              ]),
      );
}
