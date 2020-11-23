<!doctype html>
<html lang="fr">

<head>

  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <meta name="description" content="API Wiktionnaire - Récupérer et mettre en forme les définitions de termes recherchés">
  <meta name="author" content="Frédéric Gainza">
  <meta name="theme-color" content="#ffffff">

  <!-- FAVICONS -->
  <link rel="apple-touch-icon" sizes="180x180" href="assets/img/favicon-letters/apple-touch-icon.png">
  <link rel="icon" type="image/png" sizes="32x32" href="assets/img/favicon-letters/favicon-32x32.png">
  <link rel="icon" type="image/png" sizes="16x16" href="assets/img/favicon-letters/favicon-16x16.png">
  <link rel="manifest" href="assets/img/favicon-letters/site.webmanifest">
  <link rel="mask-icon" href="assets/img/favicon-letters/safari-pinned-tab.svg" color="#5bbad5">


  <title>API Wiktionnaire Fr</title>

  <link rel="canonical" href="https://api-definition.fgainza.fr">
  <link href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,300;0,400;0,900;1,400&display=swap" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Lato:ital,wght@0,300;0,400;0,700;0,900;1,400&display=swap" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Salsa&display=swap" rel="stylesheet">

  <!-- Bootstrap core CSS -->
  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" integrity="sha384-JcKb8q3iqJ61gNV9KGb8thSsNjpSL0n8PARn9HuZOnIxN0hoP+VmmDGMN5t9UJ0Z" crossorigin="anonymous">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.14.0/css/all.min.css" integrity="sha512-1PKOgIY59xJ8Co8+NE6FZ+LOAZKjy+KY8iq0G4B3CyeY6wYHN3yt9PW0XpSriVlkMXe40PTKnXrLnZ9+fkDaog==" crossorigin="anonymous" />
  <!-- CSS Perso -->
  <link rel="stylesheet" href="assets/style.css">
  <!-- GitHub Rubicon Fork Me -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/github-fork-ribbon-css/0.2.3/gh-fork-ribbon.min.css" />

</head>

<body>

  <!-- Loader -->
  <div id="bgLoader">
    <div class="loader-center">
      <div id="loaderWik"></div>
    </div>
  </div>

  <!-- Ribbon GitHub -->
  <a class="github-fork-ribbon" href="https://github.com/FredGainza/api-definition" data-ribbon="Fork me on GitHub" title="Fork me on GitHub">Fork me on GitHub</a>

  <!-- START - Layout Global -->
  <div id="layoutDefault">


    <!-- START - Layout Contenu -->
    <div id="layoutDefault_content" role="main">

      <div class="container-fluid py-2 bg-lightos header-dark">
        <div class="container">
          <h1 class="title-main text-right mr-2 mr-lg-3 my-2">API Dico Wiktionnaire</h1>
        </div>
      </div>

      <!-- ############################################################### -->
      <!-- START - SECTION FORMULAIRE -->
      <!-- ############################################################### -->
      <section class="formulaire">
        <div class="container mt-2 mt-lg-4 pb-2">
          <!-- Titre formulaire -->
          <h2 class="title-niv2 mr-3 ml-auto mr-lg-0 ml-lg-5 w-93 mt-3 mb-2">Formulaire de saisie</h2>

          <!-- START - Formulaire -->
          <form action="app/motAnalyse.php" class="mt-3" method="POST" id="formSaisie">
            <div class="row mx-auto align-items-center justify-content-center" id="validForm"></div>

            <!-- Options + Search Bar-->
            <div class="row">

              <!-- Search Bar -->
              <div class="col-12 order-1">
                <div class="form-group row w-resp mx-auto mt-1">
                  <div id="cardSearch" class="card card-sm w-100 mb-2 mb-md-4">
                    <div class="card-body lh-125 search row no-gutters align-items-center">

                      <!-- Icone loupe -->
                      <div class="col-auto loupe-search btn-bg-bord no-bord-rad">
                        <button class="btn btn-bg-bord bord-btn pad-btn" disabled>
                          <i class="fas fa-search size-search color-search mx-0 mb-0"></i>
                        </button>
                      </div>

                      <!-- Zone Input -->
                      <div class="col">
                        <input class="form-control form-control-borderless input-search" name="motInput" id="motInput" type="text" placeholder="Mot à définir" required autofocus>
                      </div>

                      <!-- Bouton validation grand ecran -->
                      <div class="col-auto bg-btn d-none d-md-flex pad-pers align-items-center">
                        <button class="btn btn-info btn-resp" id="btnEnvoiForm-lg" type="submit">Envoyer</button>
                      </div>
                      <!--end of col-->
                    </div>
                  </div>

                  <!-- Bouton validation petit ecran -->
                  <div class="d-md-none w-100 my-2">
                    <button id="btnEnvoiForm" type="submit" class="btn btn-info mx-auto py-2 btn-resp btn-block">Envoyer</button>
                  </div>
                </div>
              </div>
            </div>

          </form>
          <!-- END - Formulaire -->

        </div>
      </section>
      <!-- ############################################################### -->
      <!-- END - SECTION FORMULAIRE -->
      <!-- ############################################################### -->




      <!-- ############################################################### -->
      <!-- START - NOTIFICATION DES ERREURS -->
      <!-- ############################################################### -->
      <div id="divNotif" class="container w-90 mx-auto mt-1 my-2">
        <div id="alertError" class="alert alert-danger alert-dismissible displayMsg" role="alert"></div>
      </div>
      <!-- ############################################################### -->
      <!-- END - NOTIFICATION DES ERREURS -->
      <!-- ############################################################### -->




      <!-- ############################################################### -->
      <!-- START - SECTION MODAL DEFINITION WIKI -->
      <!-- ############################################################### -->
      <div class="def-wiki w-resp d-none" id="apiWiki">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
          <div class="modal-content">
            <div class="modal-header">
              <div class="modal-title" id="apiWikilLabel">
                <div id="motWikiAff" class="d-flex align-items-center"></div>
              </div>
            </div>
            <div id="wikiBody" class="modal-body">
              <div id="loader"></div>
              <div id="imgWikiAff"></div>
              <div id="line-separator"></div>
              <div id="defsWikiAff"></div>
              <div id="cadreComplement" class="display-cadre-complement cadre-complement my-3">
                <div id="complementAff"></div>
                <div id="line-separatorComp"></div>
                <div id="defsCompAff"></div>
              </div>
              <div id="linkPageWiki"></div>
            </div>
            <div class="modal-footer">
              <div id="CreditWiki" class="d-flex justify-content-around align-items-center credit-wiki py-1 w-90 mx-auto">
                <div class="text-center w-85 fz80-90">Définition issue du <a href="https://fr.wiktionary.org/" class="fz80-90" alt="Site du Wiktionnaire, dictionnaire francophone libre et gratuit" target="_blank">Wiktionnaire</a>,
                  dictionnaire francophone libre et gratuit.</div>
                <div class="w-20 text-center my-auto">
                  <a href="https://creativecommons.org/licenses/by-sa/3.0/fr/" class="fz80-90" target="_blank"><img class="w-logo-cc" src="assets/img/CC-BY-SA-logo.png" alt="Logo Creative Commons CC-BY-SA-" /></a>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      <!-- ############################################################### -->
      <!-- FIN - SECTION MODAL DEFINITION WIKI -->
      <!-- ############################################################### -->

    </div>
    <!-- END - Layout Contenu -->


    <!-- ############################################################### -->
    <!-- START - FOOTER -->
    <!-- ############################################################### -->
    <footer id="layoutDefault_footer" class="footer">

      <div class="container-fluid d-none d-lg-block py-1 footer-align bg-lightos footer-light">
        <div class="container d-flex flex-row justify-content-between align-items-center py-1 py-md-2">
          <div class="text-nowrap text-footer mr-auto"><a rel="license" href="http://creativecommons.org/licenses/by-nc/4.0/"><img alt="Licence Creative Commons" style="border-width:0" src="https://i.creativecommons.org/l/by-nc/4.0/88x31.png" /></a><a rel="license" href="http://creativecommons.org/licenses/by-nc/4.0/"></a></div>
          <div class="text-nowrap text-footer ml-auto"><a class="fz-norm-resp" href="https://fgainza.fr" target="_blank">Crédits</a></div>
        </div>
      </div>
    </footer>
    <!-- ############################################################### -->
    <!-- END - FOOTER -->
    <!-- ############################################################### -->

  </div>
  <!-- END - Layout Global -->

  <script src="https://code.jquery.com/jquery-3.5.1.min.js" integrity="sha256-9/aliU8dGd2tb6OSsuzixeV4y/faTqgFtohetphbbj0=" crossorigin="anonymous"></script>
  <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js" integrity="sha384-9/reFTGAW83EW2RDu2S0VKaIzap3H66lZH81PoYlFhbGU+6BZp6G7niu735Sk7lN" crossorigin="anonymous"></script>
  <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js" integrity="sha384-B4gt1jrGC7Jh4AgTPSdUtOBvfO8shuf57BaghqFfPlYxofvL8/KUEfYiJOMMV+rV" crossorigin="anonymous"></script>
  <script src="assets/app.js"></script>
</body>

</html>