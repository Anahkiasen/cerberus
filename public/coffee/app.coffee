window.action = (link, event, callback) ->
  event.preventDefault()

  $.ajax
    url: link.attr 'href'
  .done (result) ->

    # Parse JSON if it isn't already
    if Object.prototype.toString.call(result) != '[object Object]'
      result = JSON.parse(result)

    # Display any message in the results --------------------------- /

    if result.message

      # Format the message if it isn't already
      if result.message.search('alert-') is -1
        alert = if result.state then 'success' else 'error'
        result.message = '<div class="alert alert-'+alert+'">' +result.message+ '</div>'

      # Prepend to main form/table
      $('#content form, #content table').prepend result.message

    # Execute the user's callback ---------------------------------- /

    callback result