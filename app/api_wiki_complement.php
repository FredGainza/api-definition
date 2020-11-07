<?php
session_start();
if (isset($_POST['motWikiComplement']) && $_POST['motWikiComplement'] != '') {
    if (isset($dataComp) && $data != '') {
        unset($dataComp);
    }

    require "simple_html_dom.php";

    // Fonction d'affichage
    function printPre($x)
    {
        echo '<pre>';
        print_r($x);
        echo '</pre>';
    }

    // Fonction de validation des données
    function valid_donnees($donnees)
    {
        $donnees = trim($donnees);
        $donnees = stripslashes($donnees);
        $donnees = htmlspecialchars($donnees);
        return $donnees;
    }

    // Fonction pour enlever certains tags (ne pas enlever <a></a>)
    function enleveTagsPerso($chaine)
    {
        $search  = array('<i>', '</i>', '<ol>', '</ol>', '<li>', '</li>', '<pre>', '</pre>');
        $replace = array('', '', '', '', '', '', '', '');

        $chaine = str_replace($search, $replace, $chaine);
        return $chaine;
    }

    // Fonction qui vérifie l'existence d'une page web
    function IFilesExists($url)
    {
        $headers = @get_headers($url, 1);
        if ($headers[0] == '') return false;
        return !((preg_match('/404/', $headers[0])) == 1);
    }

    $motWikiComplementTemp = $_POST['motWikiComplement'];
    $motWikiComplement = valid_donnees($motWikiComplementTemp);
    $url = '';
    $errorComp = '';

    if (IFilesExists("https://fr.wiktionary.org/wiki/" . $motWikiComplement)) {
        $url = "https://fr.wiktionary.org/wiki/" . $motWikiComplement;

        $naturesGramComp = [];
        $resfinalComp = [];
        $resTempComp = [];
        $resComp = [];
        $nbNaturesGramComp = 0;
        $resFinComp = [];
        $genreTComp = [];
        $genreComp = [];

        $html = new simple_html_dom();
        $html->load_file($url);
        $nbNaturesGramComp = count($html->find('h3 span.titredef[id^=fr-]'));

        if ($nbNaturesGramComp != 0) {

            foreach ($html->find('h3 span.titredef[id^=fr-]') as $v) {
                $naturesGramComp[] = $v->plaintext;
            }

            $z = 0;
            foreach ($naturesGramComp as $i => $class) {
                if (strpos($class, "commun") !== FALSE) {
                    $x = $html->find('p span.ligne-de-forme', $z)->plaintext;
                    $genreComp[] = [$class, $x];
                    $z++;
                } else {
                    $genreComp[] = $class;
                }
            }


            for ($z = 0; $z < $nbNaturesGramComp; $z++) {

                if ($z == 0) {
                    $teteOk = false;
                    $ol_rang = 0;
                    if (null != $html->find('div[id=mw-content-text]', 0)) {
                        if (null != $html->find('div[id=mw-content-text]', 0)->find('dl', 0)) {
                            if (null != $html->find('div[id=mw-content-text]', 0)->find('dl', 0)->find('ol', 0)) {
                                $nbOl = count($html->find('div[id=mw-content-text]', 0)->find('dl', 0)->find('ol'));
                                $ol_rang = $nbOl;
                            }
                        }
                    }

                    if (null != $html->find('ol', $ol_rang)) {
                        if (null != $html->find('ol', $ol_rang)->find('li', 0)) {
                            if (null != $html->find('ol', $ol_rang)->find('li', 0)->find('span', 0)) {
                                if ($html->find('ol', $ol_rang)->find('li', 0)->find('span', 0)->plaintext == 'Linguistique') {
                                    $ol_rang++;
                                }
                            }
                        }
                    }
                }
                if ($z != 0) {
                    $ol_rang++;
                }

                $str = '';
                $str2 = '';
                $ref = '';

                $tete = $html->find('ol', $ol_rang);

                $html10 = new simple_html_dom();
                $html10->load($tete);
                foreach ($html10->find('ul') as $ul) {
                    $ul->innertext = "";
                }

                $str = $html10;
                $str2  = str_replace("<ul></ul>", "", $str);

                $html15 = new simple_html_dom();
                $html15->load($str2);

                $t = $html15->find('ol', 0);

                $ref = $t->innertext;

                $resTepComp = [];
                $resFinComp = [];
                $resOlComp = [];
                $resTComp = [];
                $resTTComp = [];
                $res2Comp = [];
                $resComp = [];
                $resTTTComp = [];
                $resTemmpComp = [];
                $resTemmp2Comp = [];

                $html20 = new simple_html_dom();
                $html20->load($ref);
                $testLi = $html20->find('li');
                foreach ($testLi as $li) {
                    $resComp[] = $li;
                }

                foreach ($resComp as $v) {
                    $test = $v->find('ol', 0);
                    if ($test != '') {
                        foreach ($test->find('li') as $li) {
                            $resTepComp[] = $li;
                        }
                    }
                }
                $resComp = array_diff($resComp, $resTepComp);

                $resStr = '';

                foreach ($resComp as $v) {
                    $resStr .= $v;
                }

                $resOlComp = explode("<ol>", $resStr);

                $nbEl = count($resOlComp);

                if ($nbEl == 1) {
                    $resTemmpComp = explode("<li>", $resOlComp[0]);
                    foreach ($resTemmpComp as $l) {
                        $res2Comp[] = $l;
                    }
                } else {
                    foreach ($resOlComp as $i => $v) {
                        if (strpos($v, "</ol>") !== FALSE) {
                            $resTTTComp = explode("</ol>", $v);

                            $resTemmpComp = explode("<li>", $resTTTComp[0]);
                            foreach ($resTemmpComp as $l) {
                                $resTComp[] = $l;
                            }
                            $res2Comp[] = [$resTComp];

                            $resTemmp2Comp = explode("<li>", $resTTTComp[1]);
                            foreach ($resTemmp2Comp as $l2) {
                                $res2Comp[] = $l2;
                            }
                        } else {
                            $resTemmpComp = explode("<li>", $v);
                            foreach ($resTemmpComp as $l) {
                                $res2Comp[] = $l;
                            }
                        }
                    }
                }

                $nbArrayComp = 0;
                foreach ($res2Comp as $i => $v) {
                    if (is_string($v)) {
                        $resFinComp[] = strip_tags($v);
                        if ($resFinComp[$i] == '') {
                            unset($resFinComp[$i]);
                        }
                    }
                    if (is_array($v)) {
                        $nbArrayComp++;
                        foreach ($v[0] as $p => $l) {
                            if ($p != 0) {
                                $resTTComp[] = strip_tags($l);
                            }
                        }
                        if ($p != 0 && $nbArrayComp > 1) {
                            $restTTUniqueComp = array_values(array_unique($resTTComp));
                            $k = array_search("", $restTTUniqueComp);
                            $resFinComp[] = [array_slice($restTTUniqueComp, $k + 1)];
                        } else {
                            $resFinComp[] = [array_keys(array_flip($resTTComp))];
                        }
                    }
                }
                $ol_rang += $nbArrayComp;
                $resfinalComp[$z][] = $resFinComp;
            }
        } else {
            $errorComp = "Un problème est survenu lors de la recherche de la définition du mot " . strtoupper($motWikiComplement);
        }
    } else {
        $errorComp = "Le mot " . strtoupper($motWikiComplement) . " n'apparait pas dans notre dictionnaire de référence, le Wiktionary.";
    }

    $dataComp = array();
    $dataComp["direct_link_comp"] = $url;
    $dataComp["motWikiComplement"] = $motWikiComplement;
    $dataComp["natureComp"] = $naturesGramComp;
    $dataComp["genreComp"] = $genreComp;
    $dataComp["natureDefComp"] = $resfinalComp;
    $dataComp["error"] = $errorComp;

    echo json_encode($dataComp);
}
