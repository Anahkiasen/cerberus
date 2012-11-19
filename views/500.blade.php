@layout('name: base')

@section('title')Erreur 500@endsection

@section('layout')
  <div class='page-header'>
    <h1>Problème serveur <small>erreur 500</small></h1>
  </div>
  <p class='lead'>Le serveur a subi une erreur durant la récupération de la page</p>
  <p>
    Tentez de revenir à {{ HTML::link(URL::home(), "la page d'accueil") }} et de recommencer. Si l'erreur subsiste nous vous encourageons
    à {{ HTML::mailto('maxime@stappler.fr', 'nous contacter') }} en précisant dans votre email la page
    que vous souhatiez atteindre (<strong>{{ str_replace(URL::base().'/', null, URL::current()) }}</strong>).
  </p>
@endsection
