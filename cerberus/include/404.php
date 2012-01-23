<? global $desired; $arbre = $desired->get(); ?>
<p class="alert alert-error">La page demandée n'existe pas</p>

<p>La page que vous avez demandé n'existe pas ou plus. Il est possible que vous ayez fait une erreur en tapant son nom.
Nous vous conseillons de revenir <?= str::slink(key($arbre), 'à l\'accueil') ?> et de rententer d'accéder à la page.</p>