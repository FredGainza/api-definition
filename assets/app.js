// Jquery
jQuery(document).ready(function ($) {

    // Attribuer le focus
    function attribuerFocus() {
        document.getElementById("motInput").focus();
    }

    // Désactiver autocomplete au formulaire    
    $('#formSaisie').attr('autocomplete', 'off');

    // Reset formulaire page index au load de la page
    window.onload = function () {
        let formSaisie = document.getElementById("formSaisie");
        formSaisie.motInput.value = '';
        $('#apiWiki').addClass('d-none');
        $('#txtNotif').html("");
        $("#motWikiAff").empty();
        $("#imgWikiAff").empty();
        $("#line-separator").empty();
        $("#defsWikiAff").empty();
        $('#defsWikiAff').empty();
        $('#linkPageWiki').empty();
        $("#complementAff").empty();
        $("#line-separatorComp").empty();
        $("#defsCompAff").empty();
        $("#cadreComplement").hide();
    };

    /*
    #############################################
    ###     START Ajax API Perso Wiki
    #############################################
    */

    function dicoWiki(e) {
        $('#txtNotif').html("");
        $("#motWikiAff").empty();
        $("#imgWikiAff").empty();
        $("#line-separator").empty();
        $("#defsWikiAff").empty();
        $('#defsWikiAff').empty();
        $('#linkPageWiki').empty();
        $("#complementAff").empty();
        $("#line-separatorComp").empty();
        $("#defsCompAff").empty();
        $("#cadreComplement").hide();

        e.preventDefault();
        $motEnter = $('#motInput').val();
        $('#motInput').val("");
        $.ajax({
            url: "app/motAnalyse.php",
            type: "POST",
            data: {
                motInput: $motEnter
            },
            dataType: "json",
            success: function (dataInput) {
                let $resMot = dataInput;
                console.log($resMot.motValid);
                console.log($resMot.error);
                let motSaisi = document.getElementById("motInput");
                if ($resMot.motValid == '') {
                    $("#bgLoader").removeClass("transparent-background");
                    $('#alertError').fadeIn().removeClass('displayMsg');
                    if ($resMot.error != '') {
                        motSaisi.value = "";
                        motSaisi.textContent = "";
                        $('#alertError').html($resMot.error);
                    } else {
                        $('#alertError').html("<i class=\"fas fa-exclamation-triangle fz110p fa-sm text-danger mr-3\"></i>Impossible d'afficher de définition pour le mot saisi.");
                    }
                    $("#loaderWik").removeClass("loader");

                    setTimeout(function () {
                        $("#alertError").fadeOut().addClass('displayMsg');
                    }, 3500);

                } else {
                    $("#loaderWik").addClass("loader");
                    $("#bgLoader").addClass("transparent-background");
                    
                    //Récupération du terme à rechercher
                    $motValid = $resMot.motValid;
                    $mot = $motValid.toLowerCase();
                    console.log($mot);

                    // Envoi AJAX à  API perso
                    $.ajax({
                        url: "app/api_wiki.php",
                        type: "POST",
                        data: {
                            motWiki: $mot
                        },
                        dataType: "json",

                        success: function (data) {
                            let $resultat = data;
                            console.log($resultat);
                            $('#apiWiki').removeClass('d-none');
                            $("#motWikiAff").html(
                                '<i class="fas ml-3 mr-3 text-info fa-lg fa-edit"></i><span class="font-weight-bold fz115-130">Définition de <span class="d-inline font-weight-bold fz130-145 text-ocre">&nbsp;&nbsp;' +
                                $resultat.motWiki.toUpperCase() +
                                "</span></span>"
                            );

                            if ($resultat.url_img != "" && $resultat.legende_img == "") {
                                $("#imgWikiAff").html(
                                    '<div class="text-center col-12 col-md-10 offset-md-1"><img src="' +
                                    $resultat.url_img +
                                    '" class="img-fluid max-img-height" alt="Illustration du terme ' +
                                    $resultat.motWiki +
                                    ' Responsive Image">' +
                                    '<span class="d-block fz80-90 text-right pr-0 mb-1">Crédits : <a class="fz80-90i" href="' +
                                    $resultat.url_credits +
                                    '" target="_blank">Commons Wikimedia</a></span></div>'
                                );
                                $("#line-separator").html('<hr class="my-0">');
                            }

                            if ($resultat.url_img != "" && $resultat.legende_img != "") {
                                $("#imgWikiAff").html(
                                    '<div class="text-center col-12 col-md-10 offset-md-1"><figure class="imgbox">' +
                                    '<a href="' +
                                    $resultat.url_img +
                                    '" target="_blank"><img src="' +
                                    $resultat.url_img +
                                    '" class="img-fluid" alt="Illustration du terme ' +
                                    $resultat.motWiki +
                                    ' Responsive Image"></a>' +
                                    "<figcaption><p>" +
                                    $resultat.legende_img +
                                    " </p></figcaption></figure>" +
                                    ' <span class="d-block small text-right fz80-90i mb-3 pr-0"><em>Crédits</em> : <a class="fz80-90i small" href="' +
                                    $resultat.url_credits +
                                    '" target="_blank">Commons Wikimedia</a></span></div>'
                                );
                                $("#line-separator").html('<hr class="my-0">');
                            }

                            $nbElements = Object.keys($resultat).length;
                            $nbNatures = 0;
                            $nbNatures = $resultat.natureDef.length;
                            if (null != $nbNatures) {
                                console.log($nbNatures);
                            } else {
                                console.log('pb');
                            }

                            $defs = "";
                            if (null != $nbNatures && $nbNatures != 0) {
                                for (let i in $resultat.nature) {
                                    let nature = "";
                                    nature =
                                        $resultat.genre[i][0] != $resultat.nature[i] ? $resultat.nature[i] : $resultat.nature[i] +
                                        ' <span class="small text-dark">(' +
                                        $resultat.genre[i][1] +
                                        ")</span>";
                                    $nbNatures = $resultat.natureDef[0].length;
                                    $defs +=
                                        '<span class="d-block fz1rem text-titre-def-popup text-left mt-3 mb-2">' +
                                        nature +
                                        "</span>";

                                    $nbNaturesDef = Object.keys($resultat.natureDef[i][0]).length;
                                    $defs += '<ul class="fa-ul">';

                                    for (let k in $resultat.natureDef[i][0]) {
                                        if (typeof $resultat.natureDef[i][0][k] === "string") {
                                            $defs +=
                                                '<li class="mb-1 ml-1 lh-12">' +
                                                '<span class="fa-li"><i class="fas fa-xs mr-1 fa-caret-right"></i></span>' +
                                                $resultat.natureDef[i][0][k] +
                                                "</li>";
                                        }

                                        if (typeof $resultat.natureDef[i][0][k] !== "string") {
                                            $nbElListe = $resultat.natureDef[i][0][k].length;
                                            $defs += '<ul class="definition mb-2">';
                                            for (let z in $resultat.natureDef[i][0][k][0]) {
                                                $defs +=
                                                    '<li class="mb-1 ml-3 lh-12"><span class="puce3">' +
                                                    $resultat.natureDef[i][0][k][0][z] +
                                                    "</span></li>";
                                            }
                                            $defs += "</ul>";
                                        }
                                    }

                                    $defs += "</ul>";
                                }
                            } else {
                                $defs +=
                                    '<span class="text-danger"> Impossible d\'afficher la définition</span><br>' +
                                    '<span class="text-dark">' + $resultat.error + '</span>';
                            }

                            $("#defsWikiAff").html($defs);
                            

                            if($resultat.direct_link != ''){
                                $('#linkPageWiki').html('<div class="text-right"><em><a class="fz80-90i small text-right" href="' +
                                    $resultat.direct_link +
                                    '" target="_blank">Page Wiktionnaire</a></em></div>');
                            }

                            // Complement formes verbales
                            for (let i = 0; i < $nbNatures; i++) {
                                $nbCompVerbe = $resultat.nature[i] == "Forme de verbe" ? $resultat.natureDef[i][0].length : 0;
                            }

                            /*#############################################
                            ###         Ajax Mot Compléméent            ###
                            #############################################*/

                            $(".click-def-complement").click((e) => {
                                e.preventDefault();

                                //Récupération ID du terme à rechercher
                                $id = e.currentTarget.id;
                                $motWikiCompId = $("#" + $id);

                                //Récupération du terme à rechercher
                                $motWikiCompTxt = $("#" + $id).text();

                                $complement = $motWikiCompTxt.toLowerCase();
                                console.log($complement);
                                $.ajax({
                                    url: "app/api_wiki_complement.php",
                                    type: "POST",
                                    data: {
                                        motWikiComplement: $complement
                                    },
                                    dataType: "json",
                                    success: function (dataComp) {
                                        let $resComp = dataComp;
                                        console.log($resComp);

                                        $("#cadreComplement").fadeIn(1000);
                                        $motWikiCompId.attr("href", "#cadreComplement");
                                        $("#complementAff").html(
                                            '<span class="font-weight-bold fz110-120">Définition de <span class="d-inline font-weight-bold fz115-130 text-blue2">' +
                                            $resComp.motWikiComplement.toUpperCase() +
                                            "</span></span>"
                                        );
                                        $("#line-separatorComp").html('<hr class="my-0">');

                                        $nbElementsComp = Object.keys($resComp).length;
                                        $nbNaturesComp = $resComp.natureDefComp.length;
                                        $defsComp = "";
                                        console.log($nbNaturesComp);
                                        if ($nbNaturesComp != 0) {
                                            for (let i = 0; i < $nbNaturesComp; i++) {
                                                let natureComp = "";
                                                natureComp =
                                                    $resComp.genreComp[i][0] != $resComp.natureComp[i] ? $resComp.natureComp[i] : $resComp.natureComp[i] +
                                                    ' <span class="small text-dark">(' +
                                                    $resComp.genreComp[i][1] +
                                                    ")</span>";
                                                $defsComp +=
                                                    '<span class="d-block fz1rem text-titre-def-popup text-left mt-4 mb-2">' +
                                                    natureComp +
                                                    "</span>";
                                                console.log(natureComp);
                                                $nbNaturesCompDef = Object.keys($resComp.natureDefComp[i][0]).length;
                                                $defsComp += '<ul class="fa-ul">';
                                                for (let k in $resComp.natureDefComp[i][0]) {
                                                    if (typeof $resComp.natureDefComp[i][0][k] === "string") {
                                                        $defsComp +=
                                                            '<li class="mb-1 ml-1 lh-12">' +
                                                            '<span class="fa-li"><i class="fas fa-xs mr-1 fa-caret-right"></i></span>' +
                                                            $resComp.natureDefComp[i][0][k] +
                                                            "</li>";
                                                    }
                                                    if (typeof $resComp.natureDefComp[i][0][k] !== "string") {
                                                        $nbElListe = $resComp.natureDefComp[i][0][k].length;
                                                        $defsComp += '<ul class="list-unstyled definition mb-2">';
                                                        for (let z in $resComp.natureDefComp[i][0][k][0]) {
                                                            $defsComp +=
                                                                '<li class="mb-1 ml-3 lh-12"><span class="puce3">' +
                                                                $resComp.natureDefComp[i][0][k][0][z] +
                                                                "</span></li>";
                                                        }
                                                        $defsComp += "</ul>";
                                                    }
                                                }
                                                $defsComp += "</ul>";
                                            }
                                        } else {
                                            $defsComp +=
                                                '<span class="text-danger"> Impossible d\'afficher la définition</span><br>' +
                                                '<span class="text-dark">' + $resComp.errorComp + '</span>';
                                        }
                                        console.log($defsComp);
                                        $("#defsCompAff").html($defsComp);

                                        if($resultat.direct_link != ''){
                                            
                                        }

                                    },
                                    error: function (resComp, statut, erreur) {
                                        console.log(resComp);
                                    },
                                });
                            });
                        },
                        complete: function () {
                            $("#loaderWik").removeClass("loader");
                            $("#bgLoader").removeClass("transparent-background");
                        },
                        error: function (resultat, statut, erreur) {
                            console.log(resultat);
                        },
                    });
                }
            },
            error: function (resMot, statut, erreur) {
                console.log(resMot);
            },

        });

    }
    $btn = ["#btnEnvoiForm", "#btnEnvoiForm-lg"];
    for (let i = 0; i < 2; i++) {
        $($btn[i]).click(dicoWiki);
    }

    // Gestion de touche "enter" pour l'input
    $("#formSaisie").keypress(function (e) {
        if ((e.keyCode == 13) && (e.target.type != "textarea")) {
            e.preventDefault();
            $(this).submit(dicoWiki(e));
        }
    });


});