<?php
session_start();

// Récupère les données POST
if(isset($_POST['motWiki']) && $_POST['motWiki'] != ''){
    // Supprime data si déjà présentes
    if(isset($data) && $data != ''){
        unset($data);
    }
    
    // ############################################################
    //      TRAVAIL PREPARATOIRE
    // ############################################################

    // Ajout des fichiers PHP
    require "simple_html_dom.php";

    // Fonction d'affichage
    function printPre($x) {
        echo '<pre>';
        print_r($x);
        echo '</pre>';
    }
    
    // Fonction de validation des données
    function valid_donnees($donnees) {
        $donnees = trim($donnees);
        $donnees = stripslashes($donnees);
        $donnees = htmlspecialchars($donnees);
        return $donnees;
    }

    // Fonction pour enlever certains tags (ne pas enlever <a></a>)
    function enleveTagsPerso($chaine) {
        $search  = array('<i>', '</i>', '<ol>', '</ol>', '<li>', '</li>', '<pre>', '</pre>');
        $replace = array('', '', '', '', '', '', '', '');

        $chaine = str_replace($search, $replace, $chaine);
        return $chaine; 
    }

    // Fonction qui vérifie l'existence d'une page web
    function IFilesExists($url) {
        $headers = @get_headers($url, 1);
        if ($headers[0] == '') return false;
        return !((preg_match('/404/', $headers[0])) == 1);
    }

    // Nettoyer les données passées en POST
    $motWikiTemp = $_POST['motWiki'];
    $motWiki = valid_donnees($motWikiTemp);


    /* ############################################################################################# */
    // Définir et Initialiser les variables
    $url = '';                  // url Wikitionary du mot recherché
    $error= '';                 // msg erreur renvoyé à l'utilisateur
    $naturesGram = [];          // tab des classes grammaticales du mot recherché
    $url_img = '';              // url de l'illustration si présente sur Wikitionnaire
    $url_credits = '';          // url de credit de la photo
    $legende_img = '';          // légende de l'image
    $resfinal = [];             // tableau final des résultats
    $nbNaturesGram = 0;         // Nb de natures grammaticales
    $resFin = [];               // tableau de résultat temp (résultat pour une classe grammaticale) 
    $genre = [];                // tableau de genre pour la classe "nom commun"
    /* ############################################################################################### */
   
    // Messsage si pas de page Wikitionnaire
    if (IFilesExists("https://fr.wiktionary.org/wiki/".$motWiki)){
        $url = "https://fr.wiktionary.org/wiki/".$motWiki;

        // ############################################################
        //      DEBUT DU PARSING
        // ############################################################

        // Parser l'ensemble de la page
        $html = new simple_html_dom();
        $html->load_file($url);

        // Cherche les span spécifiques aux classes grammaticales
        $nbNaturesGram=count($html->find('h3 span.titredef[id^=fr-]'));

        // Si il y a une ou des classes grammaticales
        if($nbNaturesGram != 0){
            // On récupère le texte des classes grammaticales   
            foreach($html->find('h3 span.titredef[id^=fr-]') as $v){
                $naturesGram[] = $v->plaintext;
            }

            // On récupère le genre pour la classe de "nom commun"
            $z=0;
            foreach($naturesGram as $i => $class){
                if ((strpos($class, "commun") !== FALSE) && (strpos($class, "Forme") === FALSE)){
                    $x = $html->find('p span.ligne-de-forme', $z)->plaintext;
                    $genre[]=[$class, $x];
                    $z++;
                }else{
                    $genre[]=$class;
                }
            }

            // s'il y a une image
            if(null!=$html->find('a.image',0)){
                // On récupère l'url du creditial 
                if(null != $html->find('div.thumbinner', 0)){
                    if(null != $html->find('div.thumbinner', 0)->find('a.image', 0)){
                        if(null != $html->find('div.thumbinner', 0)->find('a.image', 0)->getAttribute('href')){
                            $image=$html->find('div.thumbinner', 0)->find('a.image', 0)->getAttribute('href');
                            $imageTab = mb_str_split($image);
                            $imageSousTab = array_slice($imageTab, 5);
                            $imageSousStrTemp = join($imageSousTab);
                            $imageSousStr=str_replace('Fichier', 'File', $imageSousStrTemp);
                            $url_credits = "https://commons.wikimedia.org/wiki".$imageSousStr."?uselang=fr";
                        }
                    }
                }

                // On récupère l'url de l'image
                if(null != $html->find('img.thumbimage', 0)){
                    if(null != $html->find('img.thumbimage', 0)->getAttribute('srcset')){
                        $imageSrc=$html->find('img.thumbimage', 0)->getAttribute('srcset');
                        $ext=['.JPG', '.jpg', '.PNG', '.png', '.gif', '.GIF', '.tif', '.TIF', '.bmp', '.BMP', '.svg',  '.SVG'];
                        foreach($ext as $v){
                            $imageSrcExp = explode($v, $imageSrc);
                            if(isset($imageSrcExp[1])){
                                $slugSrc = "https:".$imageSrcExp[0].$v;
                                $url_img=str_replace('/thumb', '', $slugSrc);
                            }
                        }
                    }
                }

                // On récupère la légende de l'image
                if(null != $url_credits){
                    $html2 = new simple_html_dom();
                    $html2->load_file($url_credits);
                    $test = $html2->find('table', 0);
                    $test2 = $html2->find('table', 1);
                    if(count($test->find('tr')) > 3){
                        $tt = $test;
                    }else if(count($test2->find('tr')) > 3){
                            $tt = $test2;
                    }else{
                        $tt='';
                    }
                    if($tt != ''){
                        foreach($tt->find('tr') as $k => $tr){
                            if($tr->find('td[id=fileinfotpl_desc]',0) ){
                                if($tr->find('td.description', 0)){
                                    $temp=$tr->find('td.description', 0)->plaintext;
                                    $legendeSlug=strip_tags($temp);
                                    $legendNbCar = mb_strlen($legendeSlug);
                                    ($legendNbCar > 4 && $legendNbCar < 120) ? $legende_img = strip_tags($temp) : $legende_img = '';
                                }

                            }
                        }
                    }                      

                }

            }


            // ############################################################
            //      DEFINITIONS POUR CHAQUE CLASSE GRAMMATICALE
            // ############################################################

            for($z=0; $z<$nbNaturesGram; $z++){

                // Détermine à partir de quelle liste <ol> commence les définitions
                if($z == 0){
                    $teteOk = false;
                    $ol_rang = 0;

                    // On exclut les listes de symbole
                    if(null != $html->find('div[id=mw-content-text]', 0)){
                        if(null != $html->find('div[id=mw-content-text]', 0)->find('dl', 0)){
                            if(null != $html->find('div[id=mw-content-text]', 0)->find('dl', 0)->find('ol', 0)){
                                $nbOl = count($html->find('div[id=mw-content-text]', 0)->find('dl', 0)->find('ol'));
                                $ol_rang = $nbOl; 
                            }
                        }
                    }

                    // On exclut les listes d'éthymologie
                    if(null != $html->find('ol', $ol_rang)){
                        if(null != $html->find('ol', $ol_rang)->find('li', 0)){
                            if(null != $html->find('ol', $ol_rang)->find('li', 0)->find('span', 0)){
                                if($html->find('ol', $ol_rang)->find('li', 0)->find('span', 0)->plaintext == 'Linguistique'){
                                    $ol_rang++;

                                }
                            }
                        }
                    }

                }

                // on obtient $ol_rang : rang de la première liste à parser
                if($z != 0){
                    $ol_rang++;
                }
                
                // Parse la 1ère liste
                $tete=$html->find('ol', $ol_rang);

                // Si on trouve une liste ol de définition
                if($tete != ''){
                    $str='';
                    $str2='';
                    $ref='';

                    // On détermine les <ul> relatives aux exemples
                    $html3 = new simple_html_dom();
                    $html3->load($tete);
                    foreach($html3->find('ul') as $ul){
                        $ul->innertext="";
                    }

                    // Cas particulier des Formes de verbes :
                    // - on détermine le verbe à l'infinitif et on crée un lien vers celui-ci 
                    if($naturesGram[$z] == "Forme de verbe"){
                        $needLink = false;
                        $isPluriel = false;
                        foreach($html3->find('a') as $p => $a){
                            $a->id = "complement_verbe_".$p;
                            $radical = $a->title;
                            if ($radical != $motWiki){
                                $needLink = true;
                                $a->href = "https://fr.wiktionary.org/wiki/".$radical;
                                $a->class = "link-perso click-def-complement";
                            }
                        }
                    }

                    // Cas particulier des autres formes :
                    // - on détermine si c'est le pluriel qui est défini : dans ce cas, on crée un lien vers le mot au singulier 
                    if($naturesGram[$z] != "Forme de verbe"){
                        $isPluriel = false;
                        $plurielTest = $html3->find('li', 0);
                        $text=strip_tags($plurielTest);
                        $html3Tab = explode(" ", $text);

                        foreach($html3Tab as $mot){
                            if($mot == "Pluriel" || $mot == "pluriel" || (strpos($text, "Féminin singulier") !== FALSE)){
                                $linkSingulier = $html3->find('a', 0); 
                                $singulier = $linkSingulier->title;
                                $linkSingulier->href = "https://fr.wiktionary.org/wiki/".$singulier;
                                $linkSingulier->id = "complement_genre";
                                $linkSingulier->class = "link-perso click-def-complement";
                                $isPluriel = true;
                            }
                        }
                    }

                    // ############################################################
                    //      METHODE UTILISEE :
                    //          - on va déterminer toutes les <li> dans les <ol> qui restent;
                    //          - va falloir repérer les sous listes, les <ol> présentes dans 
                    //      les <ol> que l'on parse.
                    //      
                    //
                    //      Méthode choisie : faire la différence entre :
                    //
                    //          ** tab de tous les li (si sous liste "ol", il y aura une "li" qui
                    //      qui contiendra "l'intitulé" et l'"ol" avec ses "li" ) :
                    //
                    //      <li>
                    //          Intitulé
                    //          <ol>
                    //              <li>l1 de la sous-liste "ol"</li>
                    //              <li>l2 de la sous-liste "ol"</li>
                    //          </ol>
                    //      </li>
                    //
                    //         ** tab des li des sous-listes <ol>     
                    //
                    //      
                    // ############################################################


                    $str=$html3;
                    // On supprime les <ul> relatives aux exemples
                    $str2  = str_replace("<ul></ul>", "", $str);

                    $html4 = new simple_html_dom();
                    $html4->load($str2);

                    // Variables temporaires pour effectuer le tri
                    $t = $html4->find('ol', 0);

                    $ref = $t->innertext;

                    $resTep = [];
                    $resOl = [];
                    $resT = [];
                    $resTT = [];
                    $res2 = [];
                    $res = [];
                    $resTTT = [];
                    $resTemmp = [];
                    $resTemmp2 = [];
                    $resFin = [];
                    $lastDef='';

                    // Tableau de tous les <li>
                    $html5 = new simple_html_dom();
                    $html5->load($ref);
                    $testLi=$html5->find('li');
                    foreach($testLi as $li){
                        $res[]=$li;
                    }

                    // verifie si il y a une "ol" dans les "li"
                    foreach($res as $v){
                        $test = $v->find('ol', 0);
                        if($test != ''){
                            foreach($test->find('li') as $li){
                                $resTep[]=$li;
                            }
                        }
                        
                    }

                    // Si "ol" dans "li", on fait la différence
                    $res = array_diff($res, $resTep);

                    $resStr = '';

                    // On réorganise les résultats obtenus
                    foreach($res as $v){
                        $resStr .= $v;
                    }

                    $resOl = explode("<ol>", $resStr);

                    $nbEl = count($resOl);

                    /* ###################################################
                        On organise de la la façon suivante:
                            si une sous-liste "ol" : [intitulé, li_1, li_2] par exemple
                            si pas de sous liste : li 
                    */ ###################################################

                    if ($nbEl == 1){
                        $resTemmp=explode("<li>", $resOl[0]);
                            foreach($resTemmp as $l){
                                $res2[]=$l;
                            }
                    }else{
                        foreach($resOl as $i => $v){
                            if (strpos($v, "</ol>") !== FALSE){
                                $resTTT=explode("</ol>", $v);

                                $resTemmp=explode("<li>", $resTTT[0]);
                                foreach($resTemmp as $l){
                                    $resT[]=$l;
                                }
                                $res2[]=[$resT];

                                $resTemmp2=explode("<li>", $resTTT[1]);
                                foreach($resTemmp2 as $l2){
                                    $res2[]=$l2;
                                }

                            }else{
                                $resTemmp=explode("<li>", $v);
                                foreach($resTemmp as $l){
                                    $res2[]=$l;
                                }
                            }
                        }
                    }

                    // On supprime les tags html et on supprime les doublons
                    $nbArray=0;
                    foreach($res2 as $i => $v){
                        if(is_string($v)){
                            // sauf pour les cas particuliers (verbes conjugués et pluriels)
                            if(($naturesGram[$z] == "Forme de verbe" && $needLink) || $isPluriel == true){
                                $resFin[] = enleveTagsPerso($v);
                            }else{
                                $resFin[] = strip_tags($v);
                            }
                            if($resFin[$i] == ''){
                                unset($resFin[$i]);
                            }
                        }
                        if(is_array($v)){
                            $nbArray++;
                            foreach($v[0] as $p => $l){
                                if($p != 0){
                                    $resTT[]=strip_tags($l);
                                }
                            }
                            if ($p != 0 && $nbArray>1){
                                $restTTUnique =array_values(array_unique($resTT));
                                $k=array_search("", $restTTUnique);
                                $resFin[]=[array_slice($restTTUnique, $k+1)];
                            }else{

                                $resFin[]=[array_keys(array_flip($resTT))];        
                            }       
                            
                        }

                    }
                    $ol_rang += $nbArray;

                    // Le résultat de la classe grammaticale z $resTT est ajouté au tab resFinal
                    $resfinal[$z][]=$resFin;

                // Si on ne trouve pas de liste ol dans la classe grammaticale z
                }else{
                    $error = "Un problème est survenu lors de la recherche de la définition du mot ".strtoupper($motWiki);
                }

            }

        // Si on ne trouve pas de classes grammaticales
        }else{
            $error = "Un problème est survenu lors de la recherche de la définition du mot ".strtoupper($motWiki);
        }
        
    // Si le mot saisi n'est pas présent sur Wikitionnaire    
    } else {
        $error = "Le mot ".strtoupper($motWiki). " n'apparait pas dans notre dictionnaire de référence, le Wiktionary.";
    }


    // Tableau final
    $data = array();
    $data["motWiki"]=$motWiki;
    $data["error"]=$error;
    $data["direct_link"]=$url;
    $data["url_img"]=$url_img;
    $data["legende_img"]=$legende_img;
    $data["url_credits"]=$url_credits;
    $data["nature"]=$naturesGram;
    $data["genre"]=$genre;
    $data["natureDef"]=$resfinal;

    // Encodage du tableau au format JSON
    echo json_encode($data);
}