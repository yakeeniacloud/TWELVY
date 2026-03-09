# Session 09 Mars 2026

## Objectif de la session

Corriger la hiérarchie de navigation WordPress sur `headless.twelvy.net` pour qu'elle corresponde exactement à la navigation de `prostagespermis.fr` (PSP), dropdown par dropdown.

Trois dropdowns traités :
1. **LES STAGES PERMIS À POINTS**
2. **LES CONTRAVENTIONS**
3. **LE RETRAIT DE POINTS**

---

## Méthode : Comment reconstituer la hiérarchie PSP

### Étape 1 — Analyse de la base de données SQL

La BDD PSP (`khapmaitpsp_mysql_db.sql`) contient une table `contenu` avec les champs clés suivants :

| Champ | Rôle |
|-------|------|
| `actif` | 1 = visible dans la nav, 0 = caché |
| `ancre_menu` | Label court affiché dans le dropdown |
| `title_menu` | Titre alternatif du menu |
| `num_menu` | Section de nav : 1→LES STAGES, 3/4→LE RETRAIT, 5/6/7→LES CONTRAVENTIONS |
| `pos_menu` | Colonne dans le mega menu (1, 2, 3, 4) |

On a extrait les articles par `num_menu` et `actif=1` pour reconstituer la hiérarchie théorique.

### Étape 2 — Pourquoi la BDD seule n'est pas fiable

**Problème découvert** : La navigation PSP n'est **pas purement pilotée par la BDD**. Certaines pages avec `actif=0` apparaissent quand même dans le menu parce que le PHP de PSP les **hardcode manuellement** dans la nav, indépendamment du flag `actif`.

Exemple concret : **DIF - CPF** (ID=50) a `actif=0` en BDD mais est visible dans le dropdown "LES STAGES" de PSP. On l'avait donc exclu à tort lors de la première passe.

**Conséquence** : On ne peut pas reconstruire la nav à partir de la BDD seule avec 100% de fiabilité.

### Étape 3 — Vérification par screenshots

Pour pallier cette limite, tu as pris des **screenshots de la nav live de PSP** pour chaque dropdown et tu me les as fournis. Ces screenshots ont servi de **source de vérité absolue** — plus fiables que la BDD.

---

## Dropdown 1 — LES STAGES PERMIS À POINTS

**Parent WP** : ID 12

### Corrections apportées

| Action | WP ID | Titre avant | Titre après | Parent avant | Parent après |
|--------|-------|-------------|-------------|--------------|--------------|
| Title fix | 37 | "Les stages obligatoires de récupération de points" | "Stages obligatoires" | 12 | 12 |
| Title fix | 42 | "Les stages volontaires de récupération de points" | "Les stages volontaires" | 12 | 12 |
| Restauré + title | 50 | (était parent=0) | "DIF - CPF" | 0 | 12 |
| Retiré | 51 | "Comment s'inscrire" | — | 12 | 0 |
| Retiré | 53 | "Témoignages de stagiaires" | — | 12 | 0 |

**État final** : Dropdown LES STAGES correspond exactement à PSP.

---

## Dropdown 2 — LES CONTRAVENTIONS

**Parent WP** : ID 29

### Problème initial

~30 pages "actif=0" issues de migrations précédentes avaient été ajoutées comme enfants de parent=29, polluant le dropdown avec des dizaines d'articles non souhaités.

### Corrections — Nettoyage (retrait des actif=0)

Pages retirées de parent=29 → parent=0 (environ 28 pages supprimées du dropdown) :

- `/adresse-pour-contester-amende` (870)
- `/carte-radar-feu-rouge` (881)
- `/comment-contester-une-amende-en-ligne` (873)
- `/comment-contester-une-amende-par-courrier` (874)
- `/consigner-une-amende` (859)
- `/contester-amende-autre-conducteur` (858)
- `/contester-un-pv-electronique` (860)
- `/delai-de-reponse-a-ma-contestation` (871)
- `/delai-pour-contester-amende` (856)
- `/feu-rouge-point` (875)
- `/flash-radar-feu-rouge` (877)
- `/fonctionnement-radar-feu-rouge` (876)
- `/formulaire-de-requete-*` (862-865)
- `/griller-feu-rouge-jeune-conducteur` (879)
- `/modele-de-lettre-*` (866-869)
- `/motifs-pour-contester-amende` (855)
- `/pas-de-reponse-a-ma-contestation-amende` (872)
- `/reponse-contestation-amende` (861)
- `/suspension-permis-feu-rouge` (878)
- `/velo-feu-rouge` (880)
- `/vice-de-forme-amende` (857)
- Et plusieurs autres...

Aussi retirées de parent=12 (LES STAGES) qui n'auraient pas dû être là :
- ID=852 `/satisfait-ou-rembourse` → parent=0

### Corrections — Titres (parent=29 conservés)

| WP ID | Titre avant | Titre après |
|-------|-------------|-------------|
| 54 | "Interdiction de stationner" | "Stationnement interdit" |
| 58 | "Alcoolémie au volant" | "Alcool au volant" |
| 64 | "Non respect de la distance de sécurité" | "Distance de sécurité" |
| 65 | "Radars fixes" | "Radar fixe" |
| 67 | "Le radar feu rouge" | "Radar feu rouge" |
| 70 | "Contestation amende : comment s'y prendre ?" | "Contester mon amende" |
| 76 | "Amende majorée" | "Amende forfaitaire majorée" |

### Ajout de Témoignages

Page "Témoignages de stagiaires" (ID=53) ajoutée à parent=29 (LES CONTRAVENTIONS), car PSP l'affiche dans la section "Divers" de ce dropdown.

---

## Dropdown 3 — LE RETRAIT DE POINTS

**Parent WP** : ID 30

### Corrections apportées

| WP ID | Titre avant | Titre après | Parent avant | Parent après |
|-------|-------------|-------------|--------------|--------------|
| 85 | "Lettre 48N et stage de sensibilisation..." | "Lettre 48N" | 30 | 30 |
| 86 | "La lettre 48M" | "Lettre 48M" | 30 | 30 |
| 92 | "Comment contester une amende ?" | "Comment contester mon amende ?" | 0 | 30 |
| 52 | "Toutes les questions" | (inchangé) | 0 | 30 |

Note : "Lettre 48SI" (ID=87) était déjà correctement titré.

### État final

19 enfants sur 20 correspondent au screenshot PSP. Le 20ème item manquant est "Témoignages de stagiaires" — voir section ci-dessous.

---

## Problème en suspens — Témoignages de stagiaires

PSP affiche "Témoignages de stagiaires" à la fois dans LES CONTRAVENTIONS et dans LE RETRAIT DE POINTS.

WordPress n'autorise qu'un seul parent par page.

Actuellement : parent=29 (LES CONTRAVENTIONS).

**Options possibles** :
1. Laisser en parent=29 uniquement → LE RETRAIT a 19/20 items ✓ acceptable
2. Créer une 2ème page WP dupliquée pour LE RETRAIT
3. Déplacer à parent=30 → LES CONTRAVENTIONS perd l'item

**Décision** : Non encore tranchée — à confirmer avec toi.

---

## Authentification WP REST API — Point technique

L'API WP en écriture (`POST /wp-json/wp/v2/pages/{id}`) nécessite une **Application Password**, pas le mot de passe régulier.

- Mot de passe régulier (`Basklovastik9897`) → **403 Forbidden** (rejeté par Apache/OVH avant même d'atteindre WP)
- Application password (`UaSM fH38 ONVn JWda 0YSp JBcx`) → **200 OK** ✓

Trouvé dans `migrate_actif0.py` (variable `WP_APP_PASSWORD`).

Toutes les modifications WP ont été faites via scripts Python utilisant `urllib` + Basic Auth avec l'application password.

---

## Fix des pages "ancre" — Placeholder content

### Problème découvert

Certains items de la nav PSP ne sont pas des articles autonomes — ils pointent vers des **sections (#anchor) d'autres articles**. Exemples :
- "Comment contester un retrait ?" → `suspension-de-permis-et-retrait-de-permis.php#Comment contester une suspension de permis ?`
- "Comment obtenir mes accès Télépoints ?" → `p-consulter-points#Comment obtenir ses codes d'accès Télépoints ?`

Lors de la migration, des pages WP autonomes avaient été créées pour ces items avec du **contenu placeholder** (41-86 mots) au lieu du vrai contenu.

### Pages concernées (8 pages)

| WP ID | Slug | Mots | Article cible |
|-------|------|------|---------------|
| 91 | comment-obtenir-mes-acces-telepoints | 41 | consulter-ses-points |
| 69 | obtenir-mes-acces-telepoints | 57 | consulter-ses-points |
| 93 | comment-contester-un-retrait | 67 | suspension-de-permis-et-retrait-de-permis |
| 73 | recuperer-des-points-sur-mon-permis-probatoire | 74 | recuperer-ses-points |
| 95 | dans-quels-cas-faire-un-stage | 83 | ~~le-retrait-de-points~~ **retrait-de-permis** (corrigé — voir section Correction ci-dessous) |
| 94 | combien-de-temps-pour-recuperer-ses-points | 86 | stage-de-sensibilisation-a-la-securite-routiere |
| 92 | comment-contester-mon-amende | 547 | payer-son-amende |
| 90 | comment-consulter-mon-solde-de-points | 877 | consulter-ses-points |

### Solution en 3 étapes

**Étape 1 — Ajout d'attributs `id` sur les H2 des articles cibles** ✅

6 articles WP mis à jour. Tous les H2 ont reçu un attribut `id` slugifié pour permettre le scroll automatique :
- ID=81 (consulter-ses-points) → 4 H2 avec id
- ID=138 (suspension-de-permis-et-retrait-de-permis) → 8 H2 avec id
- ID=83 (recuperer-ses-points) → 5 H2 avec id
- ID=45 (stage-de-sensibilisation-a-la-securite-routiere) → 9 H2 avec id
- ID=80 (le-retrait-de-points) → 4 H2 avec id
- ID=82 (payer-son-amende) → 4 H2 avec id

**Étape 2 — Liens nav pointant directement vers article#anchor** ✅

Modifié `components/layout/Header.tsx` :
- Ajout d'un mapping `NAV_ANCHOR_LINKS` (slug → `/article-cible#anchor-id`)
- Fonction `getChildHref()` utilisée dans le rendu des liens dropdown
- Clic nav → va directement à l'article et scroll vers la section

**Étape 3 — Redirects 301 dans next.config.ts** ✅

Ajout de 8 redirections dans `next.config.ts` pour les accès directs (bookmarks, moteurs) :
```
/comment-obtenir-mes-acces-telepoints → /consulter-ses-points
/comment-contester-un-retrait → /suspension-de-permis-et-retrait-de-permis
/recuperer-des-points-sur-mon-permis-probatoire → /recuperer-ses-points
...etc
```
Note : les redirects next.config ne supportent pas les fragments `#`, mais le Header envoie directement vers l'URL avec anchor.

**Pages WP conservées published** — nécessaires pour apparaître dans la nav dropdown (la nav est construite depuis les pages WP enfants). Si on les unpublish, elles disparaissent du menu.

---

## Correction critique — `dans-quels-cas-faire-un-stage` pointait vers le mauvais article

### Problème découvert

En testant sur twelvy.net, cliquer sur "Dans quels cas faire un stage" dans le dropdown LE RETRAIT DE POINTS menait à `/le-retrait-de-points` — un article sur le **retrait de points** (déduction de points). Or sur PSP, le lien va vers `p-retrait-de-permis#Dans quels cas...` — un article sur le **retrait de permis** (suspension/annulation de permis). Ce sont deux articles complètement différents.

### Cause de l'erreur

Confusion entre "retrait de **permis**" (ID=139, slug `retrait-de-permis`) et "retrait de **points**" (ID=80, slug `le-retrait-de-points`). Le mapping initial avait été fait sans vérifier le fichier source PSP `nav2.php`.

### Vérification complète des 8 mappings via `nav2.php`

Source de vérité : `/Volumes/Crucial X9/PROSTAGES/www_3/includes/nav2.php` — le fichier PHP qui génère la navigation PSP.

| Slug Twelvy | URL PSP (nav2.php) | Target Twelvy | Correct ? |
|---|---|---|---|
| `comment-obtenir-mes-acces-telepoints` | `p-consulter-points#...` | `/consulter-ses-points#...` | ✅ |
| `obtenir-mes-acces-telepoints` | `p-consulter-points#...` | `/consulter-ses-points#...` | ✅ |
| `comment-contester-un-retrait` | `suspension-de-permis-et-retrait-de-permis.php#...` | `/suspension-de-permis-et-retrait-de-permis#...` | ✅ |
| `recuperer-des-points-sur-mon-permis-probatoire` | `p-recuperation-points#...` | `/recuperer-ses-points#...` | ✅ |
| `combien-de-temps-pour-recuperer-ses-points` | `stage-sensibilisation-securite-routiere.php#...` | `/stage-de-sensibilisation-a-la-securite-routiere#...` | ✅ |
| `dans-quels-cas-faire-un-stage` | `p-retrait-de-permis#...` | ~~`/le-retrait-de-points#...`~~ → **`/retrait-de-permis#...`** | ❌→✅ |
| `comment-contester-mon-amende` | `p-payer-amende#...` | `/payer-son-amende#...` | ✅ |
| `comment-consulter-mon-solde-de-points` | `p-consulter-points` | `/consulter-ses-points` | ✅ |

### Corrections appliquées

1. **WP page ID=139** (`retrait-de-permis`) — Ajout d'attributs `id` sur les 5 H2 :
   - `#quelles-infractions-entrainent-le-retrait-de-permis`
   - `#quels-sont-les-cas-de-retrait-de-permis`
   - `#comment-recuperer-son-permis-apres-un-retrait`
   - `#dans-quels-cas-faut-il-faire-un-stage-de-recuperation-de-points`
   - `#quels-sont-les-risques-de-conduire-sans-permis`

2. **Header.tsx** — Mapping corrigé :
   - Avant : `'dans-quels-cas-faire-un-stage': '/le-retrait-de-points#dans-quels-cas-faire-un-stage-de-sensibilisation'`
   - Après : `'dans-quels-cas-faire-un-stage': '/retrait-de-permis#dans-quels-cas-faut-il-faire-un-stage-de-recuperation-de-points'`

3. **next.config.ts** — Redirect corrigé :
   - Avant : `/dans-quels-cas-faire-un-stage` → `/le-retrait-de-points`
   - Après : `/dans-quels-cas-faire-un-stage` → `/retrait-de-permis`

4. **redirects.csv** — 4 entrées corrigées pour éviter les doubles redirections :
   - `/nombre-points-restant.php` → ~~`/comment-consulter-mon-solde-de-points`~~ → `/consulter-ses-points`
   - `/p-comment-contester-une-amende` → ~~`/comment-contester-mon-amende`~~ → `/payer-son-amende`
   - `/p-a-quel-moment-faire-un-stage` → ~~`/dans-quels-cas-faire-un-stage`~~ → `/retrait-de-permis`
   - `/p-au-bout-de-combien-de-temps-je-recupere-mes-points` → ~~`/combien-de-temps-pour-recuperer-ses-points`~~ → `/stage-de-sensibilisation-a-la-securite-routiere`

### Leçon retenue

Toujours vérifier les mappings contre le fichier source `nav2.php` de PSP, pas deviner à partir des slugs. "retrait de permis" ≠ "retrait de points".

---

## Tâches restantes

| # | Tâche | Statut |
|---|-------|--------|
| T1 | Témoignages de stagiaires — décider parent final | En attente |
| T2 | Dropdown SERVICES — vérifier et aligner sur PSP | Non commencé |
| T3 | Supprimer pages WP dupliquées (ID=36, ID=84) | Non commencé |

---

**Session mise à jour le 09 Mars 2026**
