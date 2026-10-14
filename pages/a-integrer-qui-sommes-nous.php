<?php /*
  À INTÉGRER DANS LA PAGE « QUI SOMMES-NOUS » (section Présentation)
  Contenu retiré de home.php : à partir de « Mes idées » jusqu'en bas,
  + les Missions à ajouter aux Objectifs.
  Les classes .idea-title, .modern-btn et .more-content sont définies dans
  le <style> ci-dessous : à fusionner avec la feuille de style de la page
  si elles n'y existent pas déjà.
*/ ?>
<style>
.idea-title{
    margin-top: 25px;
    font-family: var(--sans);
    font-size: 13px;
    font-weight: 700;
    letter-spacing: .08em;
    text-transform: uppercase;
    color: var(--gold);
}
.modern-btn{
    border: none;
    background: var(--ink);
    color: var(--paper);
    padding: 13px 28px;
    font-weight: 600;
    font-family: var(--sans);
    margin-top: 20px;
    transition: background .2s ease;
}
.modern-btn:hover{background: var(--ink-2); color: var(--paper);}
.more-content{
    background: var(--paper);
    border: 1px solid var(--paper-line);
    padding: 25px;
}
.more-content h4{font-family: var(--serif); font-weight:600; color: var(--ink);}
</style>

<!-- ===== Bloc déplacé depuis home.php (Mes idées -> Présentation du Parti) ===== -->
    <h3 class="idea-title">
        Mes idées
    </h3>

    <p>
        Construire une nation fondée sur les valeurs
        chrétiennes, la démocratie, la responsabilité,
        la justice et l'unité nationale.
    </p>

    <!-- BUTTON -->
    <button class="modern-btn"
            data-bs-toggle="collapse"
            data-bs-target="#moreContent">

        Lire la suite

    </button>

    <!-- COLLAPSE -->
    <div class="collapse mt-4" id="moreContent">

        <div class="more-content">

            <h4>
                Présentation du Parti
            </h4>

            <p>

                <strong>Dénomination :</strong><br>
                Rassemblement des Chrétiens Républicains (RCR)

            </p>

            <p>

                <strong>Personnalité Juridique :</strong><br>
                Arrêté N°010/2006 du 30 janvier 2006

            </p>

            <p>

                <strong>Devise :</strong>

            </p>

            <ul>

                <li>
                    <strong>Amour :</strong>
                    Amour de Dieu et du prochain
                </li>

                <li>
                    <strong>Démocratie :</strong>
                    Liberté, responsabilité et devoir
                </li>

                <li>
                    <strong>Justice :</strong>
                    Expression du droit et de la vérité
                </li>

            </ul>

        </div>

    </div>

<!-- ===== Missions à ajouter aux OBJECTIFS (Présentation + Qui sommes-nous) ===== -->
<h4>Missions</h4>
<ol>
    <li>Reconnaissance constitutionnelle de Yahweh comme Ancêtre Suprême Nationale.</li>
    <li>Assurer la répartition des Ressources nationales à tous les Citoyens propriétaires.</li>
    <li>Garantir le développement harmonieux et durable du Nekongo.</li>
</ol>
