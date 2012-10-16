window.action = (link, event, callback) ->
  event.preventDefault()

  $.ajax
    url: link.attr 'href'
  .done (result) ->

    # Display any message in the results --------------------------- /

    if result.message

      # Format the message if it isn't already
      if result.message.search('alert-') is -1
        alert = if result.state then 'success' else 'error'
        result.message = '<div class="alert alert-'+alert+'">' +result.message+ '</div>'

      # Prepend to main form/table
      $('form, table').prepend result.message

    # Execute the user's callback ---------------------------------- /

    callback result