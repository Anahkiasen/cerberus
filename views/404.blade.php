@layout('name: base')

@section('title')Erreur 404@endsection

@section('layout')
  <div class='page-header'>
    <h1>Vous êtes perdu <small>erreur 404</small></h1>
  </div>
  <p class='lead'>Il semblerait que vous soyez perdu, ou que vous Ãªtes tombÃ© sur un lien mort.</p>
  <p>
    Tentez de revenir à {{ HTML::link(URL::home(), "la page d'accueil") }} et de recommencer. Si l'erreur subsiste nous vous encourageons
    à {{ HTML::mailto('maxime@stappler.fr', 'nous contacter') }} en précisant dans votre email la page
    que vous souhatiez atteindre (<strong>{{ str_replace(URL::base().'/', null, URL::current()) }}</strong>).
  </p>
@endsection