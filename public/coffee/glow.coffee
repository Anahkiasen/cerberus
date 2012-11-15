window.Glow =

  # ATTRIBUTES -----------------------------------

  ###
  Base URL to project.
  @type String
  ###
  base: "%BASE%"

  # URL ------------------------------------------

  ###
  The URL library is responsible for
  generating URLs to framework routes.
  ###
  URL:

    ###
    Retrieve the base URL.
    @return String
    ###
    base: ->
      Glow.base

    ###
    Retrieve the URL to a route.
    @param  String  uri
    @param  Boolean   https
    @return String
    ###
    to: (uri, https = false) ->
      url = Glow.base + "/" + uri
      url.replace "http://", "https://" if https
      return url

    ###
    Retrieve the secure URL to a route.
    @param  String uri
    @return String
    ###
    to_secure: (uri) ->
      return Glow.URL.to uri, true