<?php

class MGAPI {

    var $version = "1.1";
    var $errorMessage;
    var $errorCode;

    /**
     * API server adrese
     */
    var $apiUrl;

    /**
     * Default to a 300 second timeout on server calls
     */
    var $timeout = 300;

    /**
     * Default to a 8K chunk size
     */
    var $chunkSize = 8192;

    /**
     * Lietotaja API atslega
     */
    var $api_key;

    /**
     * Izmantot ssl: false - ne, true - ja
     */
    var $secure = false;

    /**
     * Pieslegties pie MailiGen API
     * 
     * @param string $apikey Jusu MailiGen API atslega
     * @param string $secure Izmantot vai neizmantot ssl pieslegðanos
     */
    function MGAPI($apikey, $secure = false) {
        $this->secure = $secure;
        $this->apiUrl = parse_url("http://api.mailigen.com/" . $this->version . "/?output=php");
        if (isset($GLOBALS["mg_api_key"]) && $GLOBALS["mg_api_key"] != "") {
            $this->api_key = $GLOBALS["mg_api_key"];
        } else {
            $this->api_key = $GLOBALS["mg_api_key"] = $apikey;
        }
    }

    function setTimeout($seconds) {
        if (is_int($seconds)) {
            $this->timeout = $seconds;
            return true;
        }
    }

    function getTimeout() {
        return $this->timeout;
    }

    function useSecure($val) {
        if ($val === true) {
            $this->secure = true;
        } else {
            $this->secure = false;
        }
    }

    /**
     * Noòemam nost statusu, kas lika kampaòu izsûtît kaut kad nâkotnç
     *
     * @example mgapi_campaignUnschedule.php
     * @example xml-rpc_campaignUnschedule.php
     *
     * @param string $cid Kampaòas, kurai vajag noòemt izsûtîðanas laiku kaut kad nâkotnç, ID
     * @return boolean true ja ir veiksmîgi
     */
    function campaignUnschedule($cid) {
        $params = array();
        $params["cid"] = $cid;
        return $this->callServer("campaignUnschedule", $params);
    }

    /**
     * Iestâdam laiku, kad izsûtît kampaòu
     *
     * @example mgapi_campaignSchedule.php
     * @example xml-rpc_campaignSchedule.php
     *
     * @param string $cid Kampaòas, kurai vajag iestâdît izsûtîðanas laiku, ID
     * @param string $schedule_time Laiks, kad izsûtît. Laiku jânorâda ðâdâ formâtâ YYYY-MM-DD HH:II:SS pçc <strong>GMT</strong>
     * @return boolean true ja ir veiksmîgi
     */
    function campaignSchedule($cid, $schedule_time) {
        $params = array();
        $params["cid"] = $cid;
        $params["schedule_time"] = $schedule_time;
        return $this->callServer("campaignSchedule", $params);
    }

    /**
     * Atjaunojam auto atbildçtâja izsûtîðanu
     *
     * @example mgapi_campaignResume.php
     * @example xml-rpc_campaignResume.php
     *
     * @param string $cid Kampaòas, kuru vajag atsâkt, ID
     * @return boolean true ja ir veiksmîgi
     */
    function campaignResume($cid) {
        $params = array();
        $params["cid"] = $cid;
        return $this->callServer("campaignResume", $params);
    }

    /**
     * Apstâdinam uz laiku autoatbildçtâju
     *
     * @example mgapi_campaignPause.php
     * @example xml-rpc_campaignPause.php
     *
     * @param string $cid Kampaòas, kuru vajag apstâdinât, ID
     * @return boolean true ja ir veiksmîgi
     */
    function campaignPause($cid) {
        $params = array();
        $params["cid"] = $cid;
        return $this->callServer("campaignPause", $params);
    }

    /**
     * Nosûtît kampaòu nekavçjoties
     *
     * @example mgapi_campaignSendNow.php
     * @example xml-rpc_campaignSendNow.php
     *
     * @param string $cid Kampaòas, kuru vajag nosûtît, ID
     * @return boolean true ja ir veiksmîgi
     */
    function campaignSendNow($cid) {
        $params = array();
        $params["cid"] = $cid;
        return $this->callServer("campaignSendNow", $params);
    }

    /**
     * Nosûtam testa vçstuli uz norâdîtajiem epastiem
     *
     * @example mgapi_campaignSendTest.php
     * @example xml-rpc_campaignSendTest.php
     *
     * @param string $cid Kampaòas, kur vçlamies notestçt, ID
     * @param array $test_emails Masîvs, kas satur epastus, uz kuriem nosûtît vçstuli
     * @param string $send_type Nav obligâts. Ja vçlaties nosûtît abus formâtus, norâdiet "html", ja tikai teksta, tad "plain"
     * @return boolean true ja veiksmîgi
     */
    function campaignSendTest($cid, $test_emails = array(), $send_type = NULL) {
        $params = array();
        $params["cid"] = $cid;
        $params["test_emails"] = $test_emails;
        $params["send_type"] = $send_type;
        return $this->callServer("campaignSendTest", $params);
    }

    /**
     * Atrodam visus lietotâja ðablonus
     *
     * @example mgapi_campaignTemplates.php
     * @example xml-rpc_campaignTemplates.php
     *
     * @return array Masîvs, kas satur ðablonus
     * @returnf integer id Ðablona ID
     * @returnf string name Ðablona nosaukums
     * @returnf string layout Ðablona izkârtojums - "basic", "left_column", "right_column" vai "postcard"
     * @returnf string preview_image URL adrese lîdz priekðskatîjuma attçlam
     * @returnf array source Ðablona HTML kods
     */
    function campaignTemplates() {
        $params = array();
        return $this->callServer("campaignTemplates", $params);
    }

    /**
     * Izveidojam jaunu kampaòu
     *
     * @example mgapi_campaignCreate.php
     * @example xml-rpc_campaignCreate.php
     *
     * @param string $type Kampaòas veids: "html", "plain", "auto"
     * @param array $options Masîvs ar kampaòas parametriem
      string/array list_id Saraksta id, to var atrast r lists()
      string subject Vçstules virsraksts
      string from_email Epasts, uz kuru varçs nosûtît atbildes epastu
      string from_name Vârds, kas parâdîsies pie nosûtîtâja
      string to_email Merge vçrtîba, kas parâdîsies pie To: lauka (tas nav epasts)
      integer template_id Nav obligâts. Lietotâja ðablona id, nu kura tiks ìenerçts HTML saturs
      array tracking Nav obligâts. Statistikas parametru masîvs, tiek izmantotas ðâdas atslçgas: "opens", "html_clicks" un "text_clicks". Pçc noklusçjuma tiek skaitîta atvçrðana un HTML klikðíi
      string title Nav obligâts. Kampaòas nosaukums. Pçc noklusçjuma tiek izmantots vçstules virsraksts
      array analytics Nav obligâts. Masîvs ar skaitîtâju informâciju. Google gadîjumâ ir ðâds pielietojums "google"=>"jûsu_google_analytics_atslçga". "jûsu_google_analytics_atslçga" tiks pievienota visiem linkiem, statistiku varçs apskatîties klienta Google Analytics kontâ
      boolean generate_text Nav obligâts. Ja nav norâdîts plain teksts, tiks ìenerçts tekst no HTML. Pçc noklusçjuma ir false

     * @param array $content Masîvs, kas satur vçstules saturu. Struktûra:
      "html" HTML saturs
      "plain" saturs plain vçstulei
      "url" Adrese, no kuras importçt HTML tekstu. Ja netiek norâdîts plain teksts, tad vajag ieslçgt generate_text, lai tiktu ìenerçts plain teksta vçstules saturs. Ja tiek norâdîta ðî vçrtîba, tad tiek pârrakstîtas augstâk minçtâs vçrtîbas
      "archive" Ar Base64 kodçts arhîva fails. Ja tiek norâdîta ðî vçrtîba, tad tiek pârrakstîtas augstâk minçtâs vçrtîbas
      "archive_type" Nav obligâts. Pieïaujamie arhîva formâti: zip, tar.gz, tar.bz2, tar, tgz, tbz . Ja nav norâdîts, tad pçc noklusçjuma tiks izmantots zip

     * @param array $type_opts Nav obligâts - 

      Autoatbildçtâja kampaòa, ðis masîvs satur ðâdu informâciju:
      string offset-units Kâda vçrtîba no "day", "week", "month", "year". Obligâti jânorâda
      string offset-time Vçrtîba, kas ir lielâka par 0. Obligâti jânorâda
      string offset-dir Viena vçrtîba no "before" vai "after". Pçc noklusçjuma "after"
      string event Nav obligâts. Izsûtît pçc "signup" (parakstîðanâs, pçc noklusçjuma), "date" (datuma) vai "annual" (ikgadçjs)
      string event-datemerge Nav obligâts. Merge lauks, kurð tiek òemts vçrâ, kad izsûtît. Ðis ir nepiecieðams, ja event ir norâdît "date" vai "annual"

     *
     * @return string Atgrieþ jaunâs kampaòas ID
     */
    function campaignCreate($type, $options, $content, $type_opts = NULL) {
        $params = array();
        $params["type"] = $type;
        $params["options"] = $options;
        $params["content"] = $content;
        $params["type_opts"] = $type_opts;
        return $this->callServer("campaignCreate", $params);
    }

    /**
     * Atjaunojam kampaòas, kura vçl nav nosûtîta, parametrus
     *   
     *  
     *  Uzmanîbu:<br/><ul>
     *        <li>Ja Jûs izmantojat list_id, visi iepriekðçjie saraksti tiks izdzçsti.</li>
     *        <li>Ja Jûs izmantojat template_id, tiks pârrakstîts HTML saturs ar ðablona saturu</li>
     *
     * @example mgapi_campaignUpdate.php
     * @example xml-rpc_campaignUpdate.php
     *
     * @param string $cid Kampaòas, kuru vajag labot, ID
     * @param string $name Parametra nosaukums (skatîties pie campaignCreate() options lauku ). Iespçjamie parametri: subject, from_email, utt. Papildus parametri ir content. Gadîjumâ, ja vajag mainît "type_opts", kâ "name" vajag norâdît, piemçram, "auto".
     * @param mixed  $value Iespçjamâs vçrtîbas parametram ( skatîties campaignCreate() options lauku )
     * @return boolean true, ja ir veiksmîgi, pretçjâ gadîjumâ atgrieþ kïûdas paziòojumu
     */
    function campaignUpdate($cid, $name, $value) {
        $params = array();
        $params["cid"] = $cid;
        $params["name"] = $name;
        $params["value"] = $value;
        return $this->callServer("campaignUpdate", $params);
    }

    /**
     * Kopçjam kampaòu
     *
     * @example mgapi_campaignReplicate.php
     * @example xml-rpc_campaignReplicate.php
     *
     * @param string $cid Kampaòas, kuru vajag kopçt, ID
     * @return string Atgrieþam jaunâs kampaòas ID
     */
    function campaignReplicate($cid) {
        $params = array();
        $params["cid"] = $cid;
        return $this->callServer("campaignReplicate", $params);
    }

    /**
     * Tiek dzçsta neatgriezensiki kampaòa. Esiet uzmanîgi!
     *
     * @example mgapi_campaignDelete.php
     * @example xml-rpc_campaignDelete.php
     *
     * @param string $cid Kampaòas, kuru vajag dzçst, ID
     * @return boolean true ja ir veiksmîgi, pretçjâ gadîjumâ atgrieþ kïûdas paziòojumu
     */
    function campaignDelete($cid) {
        $params = array();
        $params["cid"] = $cid;
        return $this->callServer("campaignDelete", $params);
    }

    /**
     * Atgrieþam kampaòu sarakstu. Var pielietot filtru, lai detalizçt atlasîtu
     *
     * @example mgapi_campaigns.php
     * @example xml-rpc_campaigns.php
     *
     * @param array $filters Nav obligâts. Masîvs ar parametriem:
      string  campaign_id Nav obligâts, kampaòas id
      string  list_id Nav obligâts, saraksta id. To var atrast ar lists()
      string  status Nav obligâts. Var atrast kampaòu pçc statusa: sent, draft, paused, sending
      string  type Nav obligâts. Kampaòas tips: plain, html
      string  from_name Nav obligâts. Atlasa kampânu pçc nosûtîtâja vârda
      string  from_email Nav obligâts. Atlasa kampaòas pçc "Reply-to" epasta
      string  title Nav obligâts. Atlasa pçc kampaòas nosaukuma
      string  subject Nav obligâts. Atlasa pçc vçstules virsraksta ("Subject")
      string  sendtime_start Nav obligâts. Atlasa vçstules, kas izsûtîtas pçc ðî datuma/laika. Formâts - YYYY-MM-DD HH:mm:ss (24hr)
      string  sendtime_end Nav obligâts. Atlasa vçstules, kas izsûtîtas pirms ðî datuma/laika. Formâts - YYYY-MM-DD HH:mm:ss (24hr)
     * @param integer $start Nav obligâts. Lapa, no kuras izvadît datus. Pçc noklusçjuma ir 0, kas atbilst pirmajai lapai
     * @param integer $limit Nav obligâts. Rezultâtu skaits lapâ. Pçc noklusçjuma 25. Maksimâlâ vçrtîba ir 1000
     * @return array Agrieþ masîvu ar kampaòu sarakstu
     * @returnf string id Kampaòas id. To izmanto pârçjâm funkcijâm
     * @returnf integer web_id Kampaòas id, kas tiek izmanots web versijâ
     * @returnf string title Kampaòas virsraksts
     * @returnf string type Kampaòas tips (html,plain,auto)
     * @returnf date create_time Kampaòas izveidoðanas datums
     * @returnf date send_time Kampânas nosûtîðanas datums
     * @returnf integer emails_sent Epastu skaits, uz kuriem nosûtîta kampaòa
     * @returnf string status Kampaòas statuss (sent, draft, paused, sending)
     * @returnf string from_name Vârds, kas parâdâs From laukâ
     * @returnf string from_email E-pasts, uz kuru saòçmçjs var nosûtît atbildi
     * @returnf string subject Vçstules virsraksts
     * @returnf boolean to_email  Personalizçt "To:" lauku
     * @returnf string archive_url Arhîva saite uz kampaòu
     * @returnf boolean analytics Integrçt vai neitegrçt Google Analytics
     * @returnf string analytcs_tag  Google Analytics nosaukums kampaòai
     * @returnf boolean track_clicks_text Skaitît vai neskaitît klikðíus plain vçstulç
     * @returnf boolean track_clicks_html Skaitît vai neskaitît klikðíus HTML vçstulç
     * @returnf boolean track_opens Skaitît vai neskaitît atvçrðanu
     */
    function campaigns($filters = array(), $start = 0, $limit = 25) {
        $params = array();
        $params["filters"] = $filters;
        $params["start"] = $start;
        $params["limit"] = $limit;
        return $this->callServer("campaigns", $params);
    }

    /**
     * Given a list and a campaign, get all the relevant campaign statistics (opens, bounces, clicks, etc.)
     *
     * @example mgapi_campaignStats.php
     * @example xml-rpc_campaignStats.php
     *
     * @param string $cid Kampaòas id. To var atrast ar campaigns()
     * @return array Masîvs, kas satur kampaòas statistiku
     * @returnf integer hard_bounces Nepiegâdâto/nepareizo epastu skaits
     * @returnf integer soft_bounces Pagaidu nepiegâdâto 
     * @returnf integer unsubscribes Epastu skaits, kas atrakstîjâs no kampaòas
     * @returnf integer forwards Skaits, cik reizes vçstule ir pârsûtîta
     * @returnf integer opens Skaits, cik reizes atvçrts
     * @returnf date last_open Datums, kad pçdçjo reizi atvçrts
     * @returnf integer unique_opens Unikâlo atvçrðanu skait
     * @returnf integer clicks Skaits, cik daudz ir spiests uz linkiem
     * @returnf integer unique_clicks Unikâlie klikðíi uz saitçm
     * @returnf date last_click Datums, kad pçdçjo reizi spiests uz linkiem
     * @returnf integer users_who_clicked Lietotâju skaits, kas spieduði uz saitçm
     * @returnf integer emails_sent Kopçjais skaits, cik vçstules ir izsûtîtas
     */
    function campaignStats($cid) {
        $params = array();
        $params["cid"] = $cid;
        return $this->callServer("campaignStats", $params);
    }

    /**
     * Atrodam kampaòas visus linkus
     *
     * @example mgapi_campaignClickStats.php
     * @example xml-rpc_campaignClickStats.php
     *
     * @param string $cid Kampaòas id. To var atrast ar campaigns()
     * @return struct urls Saiðu masîvs, kur atslçga ir saite
     * @returnf integer clicks Kopçjais klikðíu skaits
     * @returnf integer unique Unikâlo klikðíu skaits
     */
    function campaignClickStats($cid) {
        $params = array();
        $params["cid"] = $cid;
        return $this->callServer("campaignClickStats", $params);
    }

    /**
     * Atrodam ðîs kampaòas epastu domçnu statistiku
     *
     * @example mgapi_campaignEmailDomainPerformance.php
     * @example xml-rpc_campaignEmailDomainPerformance.php
     *
     * @param string $cid Kampaòas id. To var atrast ar campaigns()
     * @return array Masîvs ar epasta domçniem
     * @returnf string domain Domçna vârds
     * @returnf integer total_sent Kopâ nosûtîto epastu skaits kampaòai (visi epasti)
     * @returnf integer emails Uz ðo domçnu nosûtîto epstu skaits
     * @returnf integer bounces Neaizgâjuðo epastu skaits
     * @returnf integer opens Unikâlo atvçrðanu skaits
     * @returnf integer clicks Unikâlo klikðíu skaits
     * @returnf integer unsubs Skaits, cik atrakstîjuðies
     * @returnf integer delivered Piegâdâto vçstuïu skaits
     * @returnf integer emails_pct Skaits, cik epastu procentuâli ir ar ðo domçnu
     * @returnf integer bounces_pct Skaits, cik procentuâli no kopçja skaita nav piegâdâts ar ðo domçnu
     * @returnf integer opens_pct Skaits, cik procentuâli ir atvçrts ar ðo domçnu
     * @returnf integer clicks_pct Skaits, cik procentuâli no ðî domçna ir spieduði
     * @returnf integer unsubs_pct Procentuâli, cik daudz no ðî domçna ir atrakstîjuðies
     */
    function campaignEmailDomainPerformance($cid) {
        $params = array();
        $params["cid"] = $cid;
        return $this->callServer("campaignEmailDomainPerformance", $params);
    }

    /**
     * Atrodam neeksistçjoðos/nepareizos epastus (hard bounces)
     *
     * @example mgapi_campaignHardBounces.php
     * @example xml-rpc_campaignHardBounces.php
     *
     * @param string $cid Kampaòas id. To var atrast ar campaigns()
     * @param integer $start Nav obligâts. Lapa, no kuras izvadît datus. Pçc noklusçjuma ir 0, kas atbilst pirmajai lapai
     * @param integer $limit Nav obligâts. Rezultâtu skaits lapâ. Pçc noklusçjuma 1000. Maksimâlâ vçrtîba ir 15000
     * @return array Epastu saraksts
     */
    function campaignHardBounces($cid, $start = 0, $limit = 1000) {
        $params = array();
        $params["cid"] = $cid;
        $params["start"] = $start;
        $params["limit"] = $limit;
        return $this->callServer("campaignHardBounces", $params);
    }

    /**
     * Atrodam pagaidu atgrieztos epastus (soft bounces)
     *
     * @example mgapi_campaignSoftBounces.php
     * @example xml-rpc_campaignSoftBounces.php
     *
     * @param string $cid Kampaòas id. To var atrast ar campaigns()
     * @param integer $start Nav obligâts. Lapa, no kuras izvadît datus. Pçc noklusçjuma ir 0, kas atbilst pirmajai lapai
     * @param integer $limit Nav obligâts. Rezultâtu skaits lapâ. Pçc noklusçjuma 1000. Maksimâlâ vçrtîba ir 15000
     * @return array Epastu saraksts
     */
    function campaignSoftBounces($cid, $start = 0, $limit = 1000) {
        $params = array();
        $params["cid"] = $cid;
        $params["start"] = $start;
        $params["limit"] = $limit;
        return $this->callServer("campaignSoftBounces", $params);
    }

    /**
     * Atrodam visus e-pastus, kas ir atrakstîjuðies no ðîs kampaòas
     *
     * @example mgapi_campaignUnsubscribes.php
     * @example xml-rpc_campaignUnsubscribes.php
     *
     * @param string $cid Kampaòas id. To var atrast ar campaigns()
     * @param integer $start Nav obligâts. Lapa, no kuras izvadît datus. Pçc noklusçjuma ir 0, kas atbilst pirmajai lapai
     * @param integer $limit Nav obligâts. Rezultâtu skaits lapâ. Pçc noklusçjuma 1000. Maksimâlâ vçrtîba ir 15000
     * @return array Epastu saraksts
     */
    function campaignUnsubscribes($cid, $start = 0, $limit = 1000) {
        $params = array();
        $params["cid"] = $cid;
        $params["start"] = $start;
        $params["limit"] = $limit;
        return $this->callServer("campaignUnsubscribes", $params);
    }

    /**
     * Atgrieþ valstu sarakstu, no kurâm ir atvçrtas vçstules un cik daudz
     *
     * @example mgapi_campaignGeoOpens.php
     * @example xml-rpc_campaignGeoOpens.php
     *
     * @param string $cid Kampaòas id. To var atrast ar campaigns()
     * @return array countries Masîvs ar valstu sarakstu
     * @returnf string code Valsts kods ISO3166 formâtâ, satur 2 simbolus
     * @returnf string name Valsts nosaukums
     * @returnf int opens Skaits, cik daudz atvçrts
     */
    function campaignGeoOpens($cid) {
        $params = array();
        $params["cid"] = $cid;
        return $this->callServer("campaignGeoOpens", $params);
    }

    /**
     * Atrodam pârsûtîðanas statistiku
     *
     * @example mgapi_campaignForwardStats.php
     * @example xml-rpc_campaignForwardStats.php
     *
     * @param string $cid Kampaòas id. To var atrast ar campaigns()
     * @param integer $start Nav obligâts. Lapa, no kuras izvadît datus. Pçc noklusçjuma ir 0, kas atbilst pirmajai lapai
     * @param integer $limit Nav obligâts. Rezultâtu skaits lapâ. Pçc noklusçjuma 1000. Maksimâlâ vçrtîba ir 15000
     * @return array Epastu saraksts
     */
    function campaignForwardStats($cid, $start = 0, $limit = 1000) {
        $params = array();
        $params["cid"] = $cid;
        $params["start"] = $start;
        $params["limit"] = $limit;
        return $this->callServer("campaignForwardStats", $params);
    }

    /**
     * Atgrieþ kampaòas atmesto vçstuïu tekstus, kuras nav vecâkas par 30 dienâm
     *
     * @example mgapi_campaignBounceMessages.php
     * @example xml-rpc_campaignBounceMessages.php
     *
     * @param string $cid Kampaòas id. To var atrast ar campaigns()
     * @param integer $start Nav obligâts. Lapa, no kuras izvadît datus. Pçc noklusçjuma ir 0, kas atbilst pirmajai lapai
     * @param integer $limit Nav obligâts. Rezultâtu skaits lapâ. Pçc noklusçjuma 25. Maksimâlâ vçrtîba ir 50
     * @return array bounces Masîvs, kas satur atsviesto epastu saturu
     * @returnf string date Laiks, kad vçstule saòemta
     * @returnf string email Epasta arese, uz kuru neizdevâs nosûtît
     * @returnf string message Atsviestçs vçstules saturs
     */
    function campaignBounceMessages($cid, $start = 0, $limit = 25) {
        $params = array();
        $params["cid"] = $cid;
        $params["start"] = $start;
        $params["limit"] = $limit;
        return $this->callServer("campaignBounceMessages", $params);
    }

    /**
     * Izveidojam jaunu sarakstu
     *
     * @example mgapi_listCreate.php
     * @example xml-rpc_listCreate.php
     *
     * @param string $title Saraksta nosaukums
     * @param array $options Masîvs ar kampaòas parametriem
      string permission_reminder Atgâdinâjums lietotâjiem, kâ tie nokïuva sarakstâ
      string notify_to Epasta adrese, uz kuru sûtît paziòojumus
      bool subscription_notify Sûtît paziòojumus par to, ka ir jauns lietotâjs pierakstîjies
      bool unsubscription_notify Sûtît paziòojumus par to, ka ir jauns lietotâjs atrakstîjies
      bool has_email_type_option Ä»aut izvçlçties epasta formâtu

     *
     * @return string Atgrieþ jaunâ saraksta ID
     */
    function listCreate($title, $options = NULL) {
        $params = array();
        $params["title"] = $title;
        $params["options"] = $options;
        return $this->callServer("listCreate", $params);
    }

    /**
     * Atjaunojam saraksta parametrus
     *
     * @example mgapi_listUpdate.php
     * @example xml-rpc_listUpdate.php
     *
     * @param string $id Saraksta, kuru vajag labot, ID
     * @param string $name Parametra nosaukums (skatîties pie listCreate() options lauku ). Iespçjamie parametri: title, permission_reminder, notify_to, utt.
     * @return boolean true, ja ir veiksmîgi, pretçjâ gadîjumâ atgrieþ kïûdas paziòojumu
     */
    function listUpdate($id, $name, $value) {
        $params = array();
        $params["id"] = $id;
        $params["name"] = $name;
        $params["value"] = $value;
        return $this->callServer("listUpdate", $params);
    }

    /**
     * Atrodam visus sarakstus
     *
     * @example mgapi_lists.php
     * @example xml-rpc_lists.php
     *
     * @return array Masîvs ar sarakstiem
     * @returnf string id Saraksta id. Ðî vçrtîba tiek izmantota cîtâs funkcijâs, kas strâdâ ar sarakstiem.
     * @returnf integer web_id Saraksta id, kas tiek izmantots web administrâcijas lapâ
     * @returnf string name Saraksta nosaukums
     * @returnf date date_created Saraksta izveidoðanas datums.
     * @returnf integer member_count Lietotâju skaits sarakstâ
     * @returnf integer unsubscribe_count Lietotâju skaits, cik atrakstîjuðies no saraksta
     * @returnf string default_from_name Noklusçjuma vçrtîba From Name priekð kampaòâm, kas izmanto ðo sarakstu
     * @returnf string default_from_email Noklusçjuma vçrtîba From Email priekð kampaòâm, kas izmanto ðo sarakstu
     * @returnf string default_subject Noklusçjuma vçrtîba Subject priekð kampaòâm, kas izmanto ðo sarakstu
     * @returnf string default_language Noklusçja valoda saraksta formâm
     */
    function lists() {
        $params = array();
        return $this->callServer("lists", $params);
    }

    /**
     * Atrodam merge tagus sarakstam
     *
     * @example mgapi_listMergeVarUpdate.php
     * @example xml-rpc_listMergeVars.php
     *
     * @param string $id Saraksta ID. Saraksta ID var atrast ar lists() metodi
     * @return array Merge tagu saraksts
     * @returnf string name Merge taga nosaukums
     * @returnf bool req Vai ðis lauks ir obligâti aizpildâms (true) vai nç (false)
     * @returnf string field_type Merge tada datu tips. Ir pieïaujamas ðâdas vçrtîbas: email, text, number, date, address, phone, website, image
     * @returnf bool show Norâda, vai râdît ðo lauku lietotâju sarakstâ.
     * @returnf string order Kârtas numurs
     * @returnf string default Vçrtîba pçc noklusçjuma
     * @returnf string tag Merge tags, kas tiek izmantots formâs, listSubscribe() un listUpdateMember()
     */
    function listMergeVars($id) {
        $params = array();
        $params["id"] = $id;
        return $this->callServer("listMergeVars", $params);
    }

    /**
     * Pievienojam jaunu merge tagu sarakstam
     *
     * @example mgapi_listMergeVarUpdate.php
     * @example xml-rpc_listMergeVarAdd.php
     *
     * @param string $id Saraksta ID. Saraksta ID var atrast ar lists() metodi
     * @param string $tag Merge tags, kuru vajag pievienot, piemçram, FNAME
     * @param string $name Garâks nosaukum, kas tiks râdîts lietotâjiem
     * @param array $options Nav obligâts. Daþâdi parametri merge tagam.
      string field_type Nav obligâts. Kâda vçrtîba no: text, number, date, address, phone, website, image. Pçc noklusçjuma ir text
      boolean req Nav obligâts. Norâda, vai lauks ir obligâti aizpildâms. Pçc noklusçjuma, false
      boolean show Nav obligâts. Norâda, vai râdît ðo lauku lietotâju sarakstâ. Pçc noklusçjuma, true
      string default_value Nav obligâts. Vçrtîba pçc noklusçjuma

     * @return boolean true ja ir izdevies, false ja nav izdevies
     */
    function listMergeVarAdd($id, $tag, $name, $options = array()) {
        $params = array();
        $params["id"] = $id;
        $params["tag"] = $tag;
        $params["name"] = $name;
        $params["options"] = $options;
        return $this->callServer("listMergeVarAdd", $params);
    }

    /**
     * Atjaunojam merge taga parametrus sarakstâ. Merge taga tipu nevar nomainît
     *
     * @example mgapi_listMergeVarUpdate.php
     * @example xml-rpc_listMergeVarUpdate.php
     *
     * @param string $id Saraksta ID. Saraksta ID var atrast ar lists() metodi
     * @param string $tag Merge tags, kuru vajag atjaunot
     * @param array $options Parametri merge taga atjaunoðanai. Pareizus parametrus skatîties pie metodes listMergeVarAdd()
     * @return boolean true ja ir izdevies, false ja nav izdevies
     */
    function listMergeVarUpdate($id, $tag, $options) {
        $params = array();
        $params["id"] = $id;
        $params["tag"] = $tag;
        $params["options"] = $options;
        return $this->callServer("listMergeVarUpdate", $params);
    }

    /**
     * Tiek izdzçsts merge tags no saraksta un vçrtîba visiem saraksta lietotâjiem. Dati tie izdzçsti neatgriezeniski
     *
     * @example mgapi_listMergeVarDel.php
     * @example xml-rpc_listMergeVarDel.php
     *
     * @param string $id Saraksta ID. Saraksta ID var atrast ar lists() metodi
     * @param string $tag Merge tags, kuru vajag izdzçst
     * @return boolean true ja ir izdevies, false ja nav izdevies
     */
    function listMergeVarDel($id, $tag) {
        $params = array();
        $params["id"] = $id;
        $params["tag"] = $tag;
        return $this->callServer("listMergeVarDel", $params);
    }

    /**
     * Pievienojam sarakstam jaunu lietotaju
     *
     * @example mgapi_listSubscribe.php
     * @example xml-rpc_listSubscribe.php
     *
     * @param string $id Saraksta ID. Saraksta ID var atrast ar lists() metodi
     * @param string $email_address Epasta adrese, ko japievieno sarakstam
     * @param array $merge_vars Masivs, kas satur MERGE lauku vertibas (FNAME, LNAME, etc.) Maksimalais izmers 255
     * @param string $email_type Nav obligats. Epasta tips: html vai plain. Pec noklusejuma html
     * @param boolean $double_optin Vai sutit apstiprinajuma vestuli. Pec noklusejuma true
     * @param boolean $update_existing Vai atjaunot eksistejoðos epastus. Pec noklusejuma false (atgriezis kludas pazinojumu)
     * @param boolean $send_welcome - Nav obligats. Sutit vai nesutit paldies vestuli. Pec noklusejuma false

     * @return boolean true ja ir izdevies, false ja nav izdevies
     */
    function listSubscribe($id, $email_address, $merge_vars, $email_type = 'html', $double_optin = true, $update_existing = false, $send_welcome = false) {
        $params = array();
        $params["id"] = $id;
        $params["email_address"] = $email_address;
        $params["merge_vars"] = $merge_vars;
        $params["email_type"] = $email_type;
        $params["double_optin"] = $double_optin;
        $params["update_existing"] = $update_existing;
        $params["send_welcome"] = $send_welcome;
        return $this->callServer("listSubscribe", $params);
    }

    /**
     * Pievienojam sarakstam jaunu lietotaju
     *
     * @example mgapi_listSubscribe.php
     * @example xml-rpc_listSubscribe.php
     *
     * @param string $id Saraksta ID. Saraksta ID var atrast ar lists() metodi
     * @param string $phone Tâlrunis, ko japievieno sarakstam
     * @param array $merge_vars Masivs, kas satur MERGE lauku vertibas (FNAME, LNAME, etc.) Maksimalais izmers 255
     * @param boolean $update_existing Vai atjaunot eksistejoðos epastus. Pec noklusejuma false (atgriezis kludas pazinojumu)

     * @return boolean true ja ir izdevies, false ja nav izdevies
     */
    function listSubscribeSMS($id, $phone, $merge_vars, $update_existing = false) {
        $params = array();
        $params["id"] = $id;
        $params["phone"] = $phone;
        $params["merge_vars"] = $merge_vars;
        $params["update_existing"] = $update_existing;
        return $this->callServer("listSubscribeSMS", $params);
    }

    /**
     * Iznemam no saraksta epasta adresi
     *
     * @example mgapi_listUnsubscribe.php
     * @example xml-rpc_listUnsubscribe.php
     *
     * @param string $id Saraksta ID. Saraksta ID var atrast ar lists() metodi
     * @param string $email_address Epasta adrese vai "id", ko var atrast ar "listMemberInfo" metodi
     * @param boolean $delete_member Dzest vai nedzest lietotaju no saraksta. Pec noklusejuma false
     * @param boolean $send_goodbye Nosutit vai nesutit pazinojumu epasta lietotajam. Pec noklusejuma true
     * @param boolean $send_notify Nosutit vai nesutit pazinojumu uz epastu, kas noradits saraksta opcijas. Pec noklusejuma false
     * @return boolean true ja ir izdevies, false ja nav izdevies
     */
    function listUnsubscribe($id, $email_address, $delete_member = false, $send_goodbye = true, $send_notify = true) {
        $params = array();
        $params["id"] = $id;
        $params["email_address"] = $email_address;
        $params["delete_member"] = $delete_member;
        $params["send_goodbye"] = $send_goodbye;
        $params["send_notify"] = $send_notify;
        return $this->callServer("listUnsubscribe", $params);
    }

    /**
     * Iznemam no saraksta epasta adresi
     *
     * @example mgapi_listUnsubscribe.php
     * @example xml-rpc_listUnsubscribe.php
     *
     * @param string $id Saraksta ID. Saraksta ID var atrast ar lists() metodi
     * @param string $phone Phone vai "id", ko var atrast ar "listMemberInfo" metodi
     * @param boolean $delete_member Dzest vai nedzest lietotaju no saraksta. Pec noklusejuma false
     * @param boolean $send_notify Nosutit vai nesutit pazinojumu uz epastu, kas noradits saraksta opcijas. Pec noklusejuma false
     * @return boolean true ja ir izdevies, false ja nav izdevies
     */
    function listUnsubscribeSMS($id, $phone, $delete_member = false, $send_notify = true) {
        $params = array();
        $params["id"] = $id;
        $params["phone"] = $phone;
        $params["delete_member"] = $delete_member;
        $params["send_notify"] = $send_notify;
        return $this->callServer("listUnsubscribeSMS", $params);
    }

    /**
     * Labo epasta adresi, MERGE laukus saraksta lietotajam
     *
     * @example mgapi_listUpdateMember.php
     * @example xml-rpc_listUpdateMember.php
     *
     * @param string $id Saraksta ID. Saraksta ID var atrast ar lists() metodi
     * @param string $email_address Epasta adrese vai "id", ko var atrast ar "listMemberInfo" metodi
     * @param array $merge_vars Masivs ar  MERGE laukiem. MERGE laukus var apskatities pie metodes "listSubscribe"
     * @param string $email_type Epasta tips: "html" vai "plain". Nav obligats
     * @return boolean true ja ir izdevies, false ja nav izdevies
     */
    function listUpdateMember($id, $email_address, $merge_vars, $email_type = '') {
        $params = array();
        $params["id"] = $id;
        $params["email_address"] = $email_address;
        $params["merge_vars"] = $merge_vars;
        $params["email_type"] = $email_type;
        return $this->callServer("listUpdateMember", $params);
    }

    /**
     * Pievienojam sarakstam vairakus epastus
     *
     * @example mgapi_listBatchSubscribe.php
     * @example xml-rpc_listBatchSubscribe.php
     *
     * @param string $id Saraksta ID. Saraksta ID var atrast ar lists() metodi
     * @param array $batch Masivs, kas satur epastu datus. Epasta dati ir masivs ar ðada atslegam: "EMAIL" epasta adresei, "EMAIL_TYPE" epasta tips (html vai plain) 
     * @param boolean $double_optin Vai sutit apstiprinajuma vestuli. Pec noklusejuma true
     * @param boolean $update_existing Vai atjaunot eksistejoðos epastus. Pec noklusejuma false (atgriezis kludas pazinojumu)
     * @return struct Masivs, kas satur skaitu, cik izevies iznemt un kludu pazinojumus
     * @returnf integer success_count Skaits, cik izdevas
     * @returnf integer error_count Skaits, cik neizdevas
     * @returnf array errors Masivs ar kludas pazinojumiem. Satur "code", "message", un "email"
     */
    function listBatchSubscribe($id, $batch, $double_optin = true, $update_existing = false) {
        $params = array();
        $params["id"] = $id;
        $params["batch"] = $batch;
        $params["double_optin"] = $double_optin;
        $params["update_existing"] = $update_existing;
        return $this->callServer("listBatchSubscribe", $params);
    }

    /**
     * Pievienojam sarakstam vairakus epastus
     *
     * @example mgapi_listBatchSubscribe.php
     * @example xml-rpc_listBatchSubscribe.php
     *
     * @param string $id Saraksta ID. Saraksta ID var atrast ar lists() metodi
     * @param array $batch Masivs, kas satur epastu datus. Epasta dati ir masivs ar ðada atslegam: "SMS" epasta adresei
     * @param boolean $update_existing Vai atjaunot eksistejoðos epastus. Pec noklusejuma false (atgriezis kludas pazinojumu)
     * @return struct Masivs, kas satur skaitu, cik izevies iznemt un kludu pazinojumus
     * @returnf integer success_count Skaits, cik izdevas
     * @returnf integer error_count Skaits, cik neizdevas
     * @returnf array errors Masivs ar kludas pazinojumiem. Satur "code", "message", un "phone"
     */
    function listBatchSubscribeSMS($id, $batch, $update_existing = false) {
        $params = array();
        $params["id"] = $id;
        $params["batch"] = $batch;
        $params["double_optin"] = $double_optin;
        $params["update_existing"] = $update_existing;
        return $this->callServer("listBatchSubscribeSMS", $params);
    }

    /**
     * Iznemam no saraksta vairakus epastus
     *
     * @example mgapi_listBatchUnsubscribe.php
     * @example xml-rpc_listBatchUnsubscribe.php
     *
     * @param string $id Saraksta ID. Saraksta ID var atrast ar lists() metodi
     * @param array $emails Epastu masivs
     * @param boolean $delete_member Dzest vai nedzest lietotaju no saraksta. Pec noklusejuma false
     * @param boolean $send_goodbye Nosutit vai nesutit pazinojumu epasta lietotajam. Pec noklusejuma true
     * @param boolean $send_notify Nosutit vai nesutit pazinojumu uz epastu, kas noradits saraksta opcijas. Pec noklusejuma false
     * @return struct Masivs, kas satur skaitu, cik izevies iznemt un kludu pazinojumus
     * @returnf integer success_count Skaits, cik izdevas
     * @returnf integer error_count Skaits, cik neizdevas
     * @returnf array errors Masivs ar kludas pazinojumiem. Satur "code", "message", un "email"
     */
    function listBatchUnsubscribe($id, $emails, $delete_member = false, $send_goodbye = true, $send_notify = false) {
        $params = array();
        $params["id"] = $id;
        $params["emails"] = $emails;
        $params["delete_member"] = $delete_member;
        $params["send_goodbye"] = $send_goodbye;
        $params["send_notify"] = $send_notify;
        return $this->callServer("listBatchUnsubscribe", $params);
    }

    /**
     * Iznemam no saraksta vairakus epastus
     *
     * @example mgapi_listBatchUnsubscribe.php
     * @example xml-rpc_listBatchUnsubscribe.php
     *
     * @param string $id Saraksta ID. Saraksta ID var atrast ar lists() metodi
     * @param array $phones Tâlruòu masivs
     * @param boolean $delete_member Dzest vai nedzest lietotaju no saraksta. Pec noklusejuma false
     * @param boolean $send_goodbye Nosutit vai nesutit pazinojumu epasta lietotajam. Pec noklusejuma true
     * @param boolean $send_notify Nosutit vai nesutit pazinojumu uz epastu, kas noradits saraksta opcijas. Pec noklusejuma false
     * @return struct Masivs, kas satur skaitu, cik izevies iznemt un kludu pazinojumus
     * @returnf integer success_count Skaits, cik izdevas
     * @returnf integer error_count Skaits, cik neizdevas
     * @returnf array errors Masivs ar kludas pazinojumiem. Satur "code", "message", un "email"
     */
    function listBatchUnsubscribeSMS($id, $phones, $delete_member = false, $send_notify = false) {
        $params = array();
        $params["id"] = $id;
        $params["phones"] = $phones;
        $params["delete_member"] = $delete_member;
        $params["send_notify"] = $send_notify;
        return $this->callServer("listBatchUnsubscribeSMS", $params);
    }

    /**
     * Atrodam epasta info sarkaksta
     *
     * @example mgapi_listMembers.php
     * @example xml-rpc_listMembers.php
     *
     * @param string $id Saraksta ID. Saraksta ID var atrast ar lists() metodi
     * @param string $status Epasta statuss (subscribed, unsubscribed, inactive, bounced), pec noklusejuma subscribed
     * @param integer $start Nav obligats. Nepiecieðams lielam sarakstam. Lapas numurs, no kuras sakt. Pirmajai lapai atbilst numurs 0
     * @param integer $limit Nav obligats. Nepiecieðams lielam sarakstam. Skaits, cik daudz atgriezt epastus. Pec noklusejuma 100, maksimalais ir 15000
     * @return array Masivs ar lietotaju sarakstu
     * @returnf string email Lietotaja epasts
     * @returnf date timestamp Peivienoðanas datums
     */
    function listMembers($id, $status = 'subscribed', $start = 0, $limit = 100) {
        $params = array();
        $params["id"] = $id;
        $params["status"] = $status;
        $params["start"] = $start;
        $params["limit"] = $limit;
        return $this->callServer("listMembers", $params);
    }

    /**
     * Atrodam epasta info sarkaksta
     *
     * @example mgapi_listMemberInfo.php
     * @example xml-rpc_listMemberInfo.php
     *
     * @param string $id Saraksta ID. Saraksta ID var atrast ar lists() metodi
     * @param string $email_address Epasta adrese vai epasta ID saraksta
     * @return array Masivs, kas satur epasta informaciju
     * @returnf string id Unikals epasta id
     * @returnf string email Epasta adrese
     * @returnf string email_type Epasta tips: html vai plain
     * @returnf array merges Masivs ar papildus laukiem
     * @returnf string status Epasta status: inactive, subscribed, unsubscribed, bounced
     * @returnf string ip_opt IP adrese, no kuras tika apstiprinats epasts
     * @returnf string ip_signup IP adrese, no kuras tika aizpildita forma
     * @returnf date timestamp Laiks, kad tika pievienots sarakstam
     */
    function listMemberInfo($id, $email_address) {
        $params = array();
        $params["id"] = $id;
        $params["email_address"] = $email_address;
        return $this->callServer("listMemberInfo", $params);
    }

    /**
     * Saraksta pieauguma informacija pa meneðiem
     *
     * @example mgapi_listGrowthHistory.php
     * @example xml-rpc_listGrowthHistory.php
     *
     * @param string $id Saraksta ID. Saraksta ID var atrast ar lists() metodi
     * @return array Masivs pa meneðiem
     * @returnf string month Gads un menesis YYYY-MM formata
     * @returnf integer existing Skaits, cik bija lietotaju meneða sakuma
     * @returnf integer imports Skaits, cik daudz tekoðaja menesi tika pievienoti lietotaji
     */
    function listGrowthHistory($id) {
        $params = array();
        $params["id"] = $id;
        return $this->callServer("listGrowthHistory", $params);
    }

    /**
     * Atrodam visus saraksta segmentus
     *
     * @example mgapi_listSegments.php
     * @example xml-rpc_listSegments.php
     *
     * @return array Masîvs ar saraksta segmentiem
     * @returnf string id Saraksta segmenta id.
     * @returnf integer web_id Saraksta segmenta id, kas tiek izmantots web administrâcijas lapâ
     * @returnf string name Saraksta segmenta nosaukums
     * @returnf date date_created Saraksta izveidoðanas datums.
     * @returnf integer member_count Lietotâju skaits sarakstâ
     */
    function listSegments($id) {
        $params = array();
        $params["id"] = $id;
        return $this->callServer("listSegments", $params);
    }

    /**
     * Atgrieþ daþadu informaciju par lietotaju kontu
     *
     * @example mgapi_getAccountDetails.php
     * @example xml-rpc_getAccountDetails.php
     *
     * @return array Masivs, kas satur daþadu informaciju par ðis API atlsegas lietotaja kontu
     * @returnf string user_id Lietotaja unikalais ID, tas tiek izmantots buvejot daþadas saites
     * @returnf string username Lietotaja lietotajvards
     * @returnf bool is_trial Vai lietotajs ir trial
     * @returnf int emails_left Skaits, cik daudz epastus var nosutit
     * @returnf datetime first_payment Pirma maksajuma datums
     * @returnf datetime last_payment Pedeja maksajuma datums
     * @returnf int times_logged_in Skaits, cik daudz reizes lietotajs caur web ir ielogojies
     * @returnf datetime last_login Datums, kad pedejo reizi bija ielogojies caur web
     * @returnf array contact Masivs, kas satur kontkatinformaciju: Vards, uzvards, epasts, uznemuma nosaukums, adrese, majas lapas adrese, telefons, fakss
     * @returnf array orders Masivs, kas satur informaciju par samaksatajiem rekiniem: rekina numurs, plans, cena, valuta, izrakstiðanas datums, pakas deriguma terminð
     */
    function getAccountDetails() {
        $params = array();
        return $this->callServer("getAccountDetails", $params);
    }

    /**
     * Atrodam visu sarakstu ID, kuros ir ðis epasts
     *
     * @example mgapi_listsForEmail.php
     * @example xml-rpc_listsForEmail.php
     *
     * @param string $email_address epasta adrese
     * @return array an array Masivs, kas satur sarakstu ID
     */
    function listsForEmail($email_address) {
        $params = array();
        $params["email_address"] = $email_address;
        return $this->callServer("listsForEmail", $params);
    }

    /**
     * Atrodam visas API atslegas
     *
     * @example mgapi_apikeys.php
     * @example xml-rpc_apikeys.php
     *
     * @param string $username lietotaja vards
     * @param string $password lietotaja parole
     * @param boolean $expired nav obligats - radit vai neradit atslegas, kuras vairs nav derigas. Pec noklusejuma ir false
     * @return array API atslegu masivs, kas satur:
     * @returnf string apikey ðo atslegu var izmantot, lai pieslegtos API
     * @returnf string created_at Datums, kad atslega ir izveidota
     * @returnf string expired_at Datums, kad ta tika atzimeta, ka neaktiva
     */
    function apikeys($username, $password, $expired = false) {
        $params = array();
        $params["username"] = $username;
        $params["password"] = $password;
        $params["expired"] = $expired;
        return $this->callServer("apikeys", $params);
    }

    /**
     * Izveidojam jaunu API atslegu
     *
     * @example mgapi_apikeyAdd.php
     * @example xml-rpc_apikeyAdd.php
     *
     * @param string $username lietotaja vards
     * @param string $password lietotaja parole
     * @return string atgrieþ jaunu API atslegu
     */
    function apikeyAdd($username, $password) {
        $params = array();
        $params["username"] = $username;
        $params["password"] = $password;
        return $this->callServer("apikeyAdd", $params);
    }

    /**
     * Atzimejam ka neaktivu API atslegu
     *
     * @example mgapi_apikeyExpire.php
     * @example xml-rpc_apikeyExpire.php
     *
     * @param string $username lietotaja vards
     * @param string $password lietotaja parole
     * @return boolean true, ja izdevas nomainit statusu
     */
    function apikeyExpire($username, $password) {
        $params = array();
        $params["username"] = $username;
        $params["password"] = $password;
        return $this->callServer("apikeyExpire", $params);
    }

    /**
     * Atrodam API atslegu
     *
     * @example mgapi_login.php
     * @example xml-rpc_login.php
     *
     * @param string $username lietotaja vards
     * @param string $password lietotaja parole
     * @return string tiek atgriezta API atslega, ja tadas vel nav, tad tiek izveidota
     */
    function login($username, $password) {
        $params = array();
        $params["username"] = $username;
        $params["password"] = $password;
        return $this->callServer("login", $params);
    }

    /**
     * "ping" - vienkarð veids, ka parbaudit, vai viss ir kartiba. Ja ir kadas problemas, tiks atgriezts par to pazinojums.
     *
     * @example mgapi_ping.php
     * @example xml-rpc_ping.php
     *
     * @return string tiek atgriezts teksts "Everything's Ok!", ja viss ir kartiba, ja nav, tad atgrieþ kludas pazinojumu
     */
    function ping() {
        $params = array();
        return $this->callServer("ping", $params);
    }

    /**
     * Piesledzas pie servera uz izsauc nepiecieðamo metodi un atgrieþ rezultatu
     * ðo funkciju nav nepiecieðams izsaukt manuali
     */
    function callServer($method, $params) {
        $host = $this->apiUrl["host"];
        $params["apikey"] = $this->api_key;

        $this->errorMessage = "";
        $this->errorCode = "";
        $post_vars = $this->httpBuildQuery($params);

        $payload = "POST " . $this->apiUrl["path"] . "?" . $this->apiUrl["query"] . "&method=" . $method . " HTTP/1.0\r\n";
        $payload .= "Host: " . $host . "\r\n";
        $payload .= "User-Agent: MGAPI/" . $this->version . "\r\n";
        $payload .= "Content-type: application/x-www-form-urlencoded\r\n";
        $payload .= "Content-length: " . strlen($post_vars) . "\r\n";
        $payload .= "Connection: close \r\n\r\n";
        $payload .= $post_vars;

        ob_start();
        if ($this->secure) {
            $sock = fsockopen("ssl://" . $host, 443, $errno, $errstr, 30);
        } else {
            $sock = fsockopen($host, 80, $errno, $errstr, 30);
        }
        if (!$sock) {
            $this->errorMessage = "Could not connect (ERR $errno: $errstr)";
            $this->errorCode = "-99";
            ob_end_clean();
            return false;
        }

        $response = "";
        fwrite($sock, $payload);
        stream_set_timeout($sock, $this->timeout);
        $info = stream_get_meta_data($sock);
        while ((!feof($sock)) && (!$info["timed_out"])) {
            $response .= fread($sock, $this->chunkSize);
            $info = stream_get_meta_data($sock);
        }
        if ($info["timed_out"]) {
            $this->errorMessage = "Could not read response (timed out)";
            $this->errorCode = -98;
        }
        fclose($sock);
        ob_end_clean();
        if ($info["timed_out"])
            return false;

        list($throw, $response) = explode("\r\n\r\n", $response, 2);

        if (ini_get("magic_quotes_runtime"))
            $response = stripslashes($response);

        $serial = unserialize($response);
        if ($response && $serial === false) {
            $response = array("error" => "Bad Response.  Got This: " . $response, "code" => "-99");
        } else {
            $response = $serial;
        }
        if (is_array($response) && isset($response["error"])) {
            $this->errorMessage = $response["error"];
            $this->errorCode = $response["code"];
            return false;
        }

        return $response;
    }

    /**
     * Definejam funkciju, kas aizstaj http_build_query sistemam, kuras tas nav
     */
    function httpBuildQuery($params, $key = NULL) {
        if (!function_exists('http_build_query')) {
            $ret = array();

            foreach ((array) $params as $name => $val) {
                $name = urlencode($name);
                if ($key !== null) {
                    $name = $key . "[" . $name . "]";
                }

                if (is_array($val) || is_object($val)) {
                    $ret[] = $this->httpBuildQuery($val, $name);
                } elseif ($val !== null) {
                    $ret[] = $name . "=" . urlencode($val);
                }
            }

            return implode("&", $ret);
        } else {
            return http_build_query((array) $params, $key);
        }
    }

}

?>