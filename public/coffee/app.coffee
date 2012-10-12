window.action = (link, event, callback) ->
  event.preventDefault()

  $.ajax
    url: link.attr 'href'
  .done (result) -> callback result