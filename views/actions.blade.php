@if (isset($item->id))
  {{ Former::hidden('id', $item->id) }}
@endif
{{ Former::actions()
  ->large_primary_submit('Créer/Modifier l\'élement')
  ->large_link('Revenir', '#') }}