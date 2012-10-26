@if($item->id)
  {{ Former::hidden('id', $item->id) }}
@endif
{{
  Former::actions
  (
    Button::large_primary_submit(Babel::create()->verb($mode)),
    Button::large_inverse_link(action($page.'@index'), Babel::back())
  )
}}