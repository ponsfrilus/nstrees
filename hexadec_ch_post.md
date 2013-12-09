Note: ceci est une transcription en markdown du post original de la class sur hexadec.ch, <http://www.hexadec.ch/hexalab/article/nested-sets>


# Nested Sets

Sous cette expression anglaise se cache une notion tout à fait commune à chacun d'entre nous, une structure hiérarchique. Une représentation hiérarchique d'éléments permet à l'Homme de les situer les uns par rapport aux autres.

Il existe plusieurs modèles de structures de données pour représenter une hiérarchie d'éléments, celle faisant l'objet de cet article a plusieurs noms en fait:

- Nested sets (traduction française : Ensembles Nichés).
- Modified Preorder Tree Traversal (traduction française : Parcours préordonné modifié d'arbre).

Un autre modèle courant est le modèle adjacent. C'est un des modèles les plus utilisés de par sa simplicité, chaque nœud est lié à son parent le plus proche. Malheureusement il faut utiliser la récursivité pour parcourir un arbre de ce type complètement. Cela peut devenir gênant lorsque la taille de l'arbre est importante.

En revanche, la maintenance d'un arbre (ajout, modification, suppression de nœuds) basée sur le modèle adjacent est très rapide et simple à mettre en œuvre.

Les Nested Sets quant à eux permettent un parcours de l'arbre complet optimisé (puisque préordonné), mais ils ont une maintenance plus lourde et moins aisée.

Il s'agit donc de choisir la solution la plus appropriée au cas à traiter. Dans le cas des Nested Sets, voici quelques applications possibles :

- Catégories d'un forum / sites / magasin / ...
- Menu de navigation
- Organigramme

Le dénominateur commun de ces quelques applications est que la structure n'est modifiée que rarement, mais est parcourue intensivement !

Il serait par contre illogique d'utiliser ce modèle dans le cas, par exemple, d'un forum au niveau de la hiérarchie des messages. La structure étant modifiée souvent il est préférable d'utiliser un modèle adjacent.

Le fonctionnement des Nested Sets est le suivant, chaque nœud de l'arbre contient une référence au nœud "gauche" et au nœud "droite". Cela sert à décrire le parcours à travers l'arbre et également à définir les niveaux hiérarchiques. Par exemple on peut dire que chaque nœud ayant un écart de plus de 1 entre la référence "gauche" et "droite" a au moins un enfant. On peu même trouver le nombre d'enfants en calculant : ("droite" - "gauche" - 1) / 2 .




![Figure 1 — Parcours préordonné d'un arbre](./nstrees.png "Schéma expliquant le parcours préordré")


Un autre avantage de cette structure est de pouvoir récupérer l'arbre en entier ou partiellement en une seule requête sur la base de données, sans devoir recourir à la récursivité. Ce qui en cas d'affichage courant de l'arbre permet d'utiliser le minimum de ressources possibles.

La difficulté se situe dans la maintenance de l'arbre, car par exemple l'ajout d'un nœud dans la structure implique une mise à jour de tous les nœuds en aval de celui-ci. Il en va de même pour les autres opérations de maintenance.

J'ai été amené à développer une classe pour l'accès et la maintenance de cette structure de Nested Sets. Il existe une bibliothèque de fonctions, créé par Rolf Brugger ([http://www.edutech.ch/](edutech)), nommée [http://www.edutech.ch/contribution/nstrees/](nstrees library) (sous licence [http://www.gnu.org/copyleft](GNU/GPL)).

En me basant sur ceci, j'ai développé un classe PHP permettant toutes ces opérations et quelques autres aussi. Voici quelques améliorations apportées :

- L'utilisation d'une classe permet une gestion de plusieurs arbres indépendants.
- Suppression d'un nœud : Conserver les enfants et les désimbriquer.
- Breadcrumbs Path, chemin vers un nœud sous forme de tableau et non-dépendant de la modélisation de la base de données (nom du champ): nstGetBreadcrumbsPath. (Ce nom vient de la fable du petit poucet laissant des morceaux de pain, beadcrumbs en anglais, pour retrouver son chemin)
- Récupération de tous les champs concernant un nœud : nstNodeAllAttributes.
- Toutes les méthodes et propriétés sont commentées.
- Aide à la compréhension sur le parcours de l'arbre inclus sous la forme de commentaires.

Code source de cette classe : [http://www.hexadec.ch/sites/default/files/global/hexalab/nestedsets/nstrees.class.phps](nstrees.class.phps)
Code source d'un exemple d'utilisation : [http://www.hexadec.ch/sites/default/files/global/hexalab/nestedsets/nstreestest.phps](nstreestest.phps)


## Références supplémentaires

- [http://www.sitepoint.com/article/hierarchical-data-database/2](Storing Hierarchical Data in a Database)
