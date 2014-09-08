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
     * @param string $secure Izmantot vai neizmantot ssl piesleg�anos
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
     * No�emam nost statusu, kas lika kampa�u izs�t�t kaut kad n�kotn�
     *
     * @example mgapi_campaignUnschedule.php
     * @example xml-rpc_campaignUnschedule.php
     *
     * @param string $cid Kampa�as, kurai vajag no�emt izs�t��anas laiku kaut kad n�kotn�, ID
     * @return boolean true ja ir veiksm�gi
     */
    function campaignUnschedule($cid) {
        $params = array();
        $params["cid"] = $cid;
        return $this->callServer("campaignUnschedule", $params);
    }

    /**
     * Iest�dam laiku, kad izs�t�t kampa�u
     *
     * @example mgapi_campaignSchedule.php
     * @example xml-rpc_campaignSchedule.php
     *
     * @param string $cid Kampa�as, kurai vajag iest�d�t izs�t��anas laiku, ID
     * @param string $schedule_time Laiks, kad izs�t�t. Laiku j�nor�da ��d� form�t� YYYY-MM-DD HH:II:SS p�c <strong>GMT</strong>
     * @return boolean true ja ir veiksm�gi
     */
    function campaignSchedule($cid, $schedule_time) {
        $params = array();
        $params["cid"] = $cid;
        $params["schedule_time"] = $schedule_time;
        return $this->callServer("campaignSchedule", $params);
    }

    /**
     * Atjaunojam auto atbild�t�ja izs�t��anu
     *
     * @example mgapi_campaignResume.php
     * @example xml-rpc_campaignResume.php
     *
     * @param string $cid Kampa�as, kuru vajag ats�kt, ID
     * @return boolean true ja ir veiksm�gi
     */
    function campaignResume($cid) {
        $params = array();
        $params["cid"] = $cid;
        return $this->callServer("campaignResume", $params);
    }

    /**
     * Apst�dinam uz laiku autoatbild�t�ju
     *
     * @example mgapi_campaignPause.php
     * @example xml-rpc_campaignPause.php
     *
     * @param string $cid Kampa�as, kuru vajag apst�din�t, ID
     * @return boolean true ja ir veiksm�gi
     */
    function campaignPause($cid) {
        $params = array();
        $params["cid"] = $cid;
        return $this->callServer("campaignPause", $params);
    }

    /**
     * Nos�t�t kampa�u nekav�joties
     *
     * @example mgapi_campaignSendNow.php
     * @example xml-rpc_campaignSendNow.php
     *
     * @param string $cid Kampa�as, kuru vajag nos�t�t, ID
     * @return boolean true ja ir veiksm�gi
     */
    function campaignSendNow($cid) {
        $params = array();
        $params["cid"] = $cid;
        return $this->callServer("campaignSendNow", $params);
    }

    /**
     * Nos�tam testa v�stuli uz nor�d�tajiem epastiem
     *
     * @example mgapi_campaignSendTest.php
     * @example xml-rpc_campaignSendTest.php
     *
     * @param string $cid Kampa�as, kur v�lamies notest�t, ID
     * @param array $test_emails Mas�vs, kas satur epastus, uz kuriem nos�t�t v�stuli
     * @param string $send_type Nav oblig�ts. Ja v�laties nos�t�t abus form�tus, nor�diet "html", ja tikai teksta, tad "plain"
     * @return boolean true ja veiksm�gi
     */
    function campaignSendTest($cid, $test_emails = array(), $send_type = NULL) {
        $params = array();
        $params["cid"] = $cid;
        $params["test_emails"] = $test_emails;
        $params["send_type"] = $send_type;
        return $this->callServer("campaignSendTest", $params);
    }

    /**
     * Atrodam visus lietot�ja �ablonus
     *
     * @example mgapi_campaignTemplates.php
     * @example xml-rpc_campaignTemplates.php
     *
     * @return array Mas�vs, kas satur �ablonus
     * @returnf integer id �ablona ID
     * @returnf string name �ablona nosaukums
     * @returnf string layout �ablona izk�rtojums - "basic", "left_column", "right_column" vai "postcard"
     * @returnf string preview_image URL adrese l�dz priek�skat�juma att�lam
     * @returnf array source �ablona HTML kods
     */
    function campaignTemplates() {
        $params = array();
        return $this->callServer("campaignTemplates", $params);
    }

    /**
     * Izveidojam jaunu kampa�u
     *
     * @example mgapi_campaignCreate.php
     * @example xml-rpc_campaignCreate.php
     *
     * @param string $type Kampa�as veids: "html", "plain", "auto"
     * @param array $options Mas�vs ar kampa�as parametriem
      string/array list_id Saraksta id, to var atrast r lists()
      string subject V�stules virsraksts
      string from_email Epasts, uz kuru var�s nos�t�t atbildes epastu
      string from_name V�rds, kas par�d�sies pie nos�t�t�ja
      string to_email Merge v�rt�ba, kas par�d�sies pie To: lauka (tas nav epasts)
      integer template_id Nav oblig�ts. Lietot�ja �ablona id, nu kura tiks �ener�ts HTML saturs
      array tracking Nav oblig�ts. Statistikas parametru mas�vs, tiek izmantotas ��das atsl�gas: "opens", "html_clicks" un "text_clicks". P�c noklus�juma tiek skait�ta atv�r�ana un HTML klik��i
      string title Nav oblig�ts. Kampa�as nosaukums. P�c noklus�juma tiek izmantots v�stules virsraksts
      array analytics Nav oblig�ts. Mas�vs ar skait�t�ju inform�ciju. Google gad�jum� ir ��ds pielietojums "google"=>"j�su_google_analytics_atsl�ga". "j�su_google_analytics_atsl�ga" tiks pievienota visiem linkiem, statistiku var�s apskat�ties klienta Google Analytics kont�
      boolean generate_text Nav oblig�ts. Ja nav nor�d�ts plain teksts, tiks �ener�ts tekst no HTML. P�c noklus�juma ir false

     * @param array $content Mas�vs, kas satur v�stules saturu. Strukt�ra:
      "html" HTML saturs
      "plain" saturs plain v�stulei
      "url" Adrese, no kuras import�t HTML tekstu. Ja netiek nor�d�ts plain teksts, tad vajag iesl�gt generate_text, lai tiktu �ener�ts plain teksta v�stules saturs. Ja tiek nor�d�ta �� v�rt�ba, tad tiek p�rrakst�tas augst�k min�t�s v�rt�bas
      "archive" Ar Base64 kod�ts arh�va fails. Ja tiek nor�d�ta �� v�rt�ba, tad tiek p�rrakst�tas augst�k min�t�s v�rt�bas
      "archive_type" Nav oblig�ts. Pie�aujamie arh�va form�ti: zip, tar.gz, tar.bz2, tar, tgz, tbz . Ja nav nor�d�ts, tad p�c noklus�juma tiks izmantots zip

     * @param array $type_opts Nav oblig�ts - 

      Autoatbild�t�ja kampa�a, �is mas�vs satur ��du inform�ciju:
      string offset-units K�da v�rt�ba no "day", "week", "month", "year". Oblig�ti j�nor�da
      string offset-time V�rt�ba, kas ir liel�ka par 0. Oblig�ti j�nor�da
      string offset-dir Viena v�rt�ba no "before" vai "after". P�c noklus�juma "after"
      string event Nav oblig�ts. Izs�t�t p�c "signup" (parakst��an�s, p�c noklus�juma), "date" (datuma) vai "annual" (ikgad�js)
      string event-datemerge Nav oblig�ts. Merge lauks, kur� tiek �emts v�r�, kad izs�t�t. �is ir nepiecie�ams, ja event ir nor�d�t "date" vai "annual"

     *
     * @return string Atgrie� jaun�s kampa�as ID
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
     * Atjaunojam kampa�as, kura v�l nav nos�t�ta, parametrus
     *   
     *  
     *  Uzman�bu:<br/><ul>
     *        <li>Ja J�s izmantojat list_id, visi iepriek��jie saraksti tiks izdz�sti.</li>
     *        <li>Ja J�s izmantojat template_id, tiks p�rrakst�ts HTML saturs ar �ablona saturu</li>
     *
     * @example mgapi_campaignUpdate.php
     * @example xml-rpc_campaignUpdate.php
     *
     * @param string $cid Kampa�as, kuru vajag labot, ID
     * @param string $name Parametra nosaukums (skat�ties pie campaignCreate() options lauku ). Iesp�jamie parametri: subject, from_email, utt. Papildus parametri ir content. Gad�jum�, ja vajag main�t "type_opts", k� "name" vajag nor�d�t, piem�ram, "auto".
     * @param mixed  $value Iesp�jam�s v�rt�bas parametram ( skat�ties campaignCreate() options lauku )
     * @return boolean true, ja ir veiksm�gi, pret�j� gad�jum� atgrie� k��das pazi�ojumu
     */
    function campaignUpdate($cid, $name, $value) {
        $params = array();
        $params["cid"] = $cid;
        $params["name"] = $name;
        $params["value"] = $value;
        return $this->callServer("campaignUpdate", $params);
    }

    /**
     * Kop�jam kampa�u
     *
     * @example mgapi_campaignReplicate.php
     * @example xml-rpc_campaignReplicate.php
     *
     * @param string $cid Kampa�as, kuru vajag kop�t, ID
     * @return string Atgrie�am jaun�s kampa�as ID
     */
    function campaignReplicate($cid) {
        $params = array();
        $params["cid"] = $cid;
        return $this->callServer("campaignReplicate", $params);
    }

    /**
     * Tiek dz�sta neatgriezensiki kampa�a. Esiet uzman�gi!
     *
     * @example mgapi_campaignDelete.php
     * @example xml-rpc_campaignDelete.php
     *
     * @param string $cid Kampa�as, kuru vajag dz�st, ID
     * @return boolean true ja ir veiksm�gi, pret�j� gad�jum� atgrie� k��das pazi�ojumu
     */
    function campaignDelete($cid) {
        $params = array();
        $params["cid"] = $cid;
        return $this->callServer("campaignDelete", $params);
    }

    /**
     * Atgrie�am kampa�u sarakstu. Var pielietot filtru, lai detaliz�t atlas�tu
     *
     * @example mgapi_campaigns.php
     * @example xml-rpc_campaigns.php
     *
     * @param array $filters Nav oblig�ts. Mas�vs ar parametriem:
      string  campaign_id Nav oblig�ts, kampa�as id
      string  list_id Nav oblig�ts, saraksta id. To var atrast ar lists()
      string  status Nav oblig�ts. Var atrast kampa�u p�c statusa: sent, draft, paused, sending
      string  type Nav oblig�ts. Kampa�as tips: plain, html
      string  from_name Nav oblig�ts. Atlasa kamp�nu p�c nos�t�t�ja v�rda
      string  from_email Nav oblig�ts. Atlasa kampa�as p�c "Reply-to" epasta
      string  title Nav oblig�ts. Atlasa p�c kampa�as nosaukuma
      string  subject Nav oblig�ts. Atlasa p�c v�stules virsraksta ("Subject")
      string  sendtime_start Nav oblig�ts. Atlasa v�stules, kas izs�t�tas p�c �� datuma/laika. Form�ts - YYYY-MM-DD HH:mm:ss (24hr)
      string  sendtime_end Nav oblig�ts. Atlasa v�stules, kas izs�t�tas pirms �� datuma/laika. Form�ts - YYYY-MM-DD HH:mm:ss (24hr)
     * @param integer $start Nav oblig�ts. Lapa, no kuras izvad�t datus. P�c noklus�juma ir 0, kas atbilst pirmajai lapai
     * @param integer $limit Nav oblig�ts. Rezult�tu skaits lap�. P�c noklus�juma 25. Maksim�l� v�rt�ba ir 1000
     * @return array Agrie� mas�vu ar kampa�u sarakstu
     * @returnf string id Kampa�as id. To izmanto p�r�j�m funkcij�m
     * @returnf integer web_id Kampa�as id, kas tiek izmanots web versij�
     * @returnf string title Kampa�as virsraksts
     * @returnf string type Kampa�as tips (html,plain,auto)
     * @returnf date create_time Kampa�as izveido�anas datums
     * @returnf date send_time Kamp�nas nos�t��anas datums
     * @returnf integer emails_sent Epastu skaits, uz kuriem nos�t�ta kampa�a
     * @returnf string status Kampa�as statuss (sent, draft, paused, sending)
     * @returnf string from_name V�rds, kas par�d�s From lauk�
     * @returnf string from_email E-pasts, uz kuru sa��m�js var nos�t�t atbildi
     * @returnf string subject V�stules virsraksts
     * @returnf boolean to_email  Personaliz�t "To:" lauku
     * @returnf string archive_url Arh�va saite uz kampa�u
     * @returnf boolean analytics Integr�t vai neitegr�t Google Analytics
     * @returnf string analytcs_tag  Google Analytics nosaukums kampa�ai
     * @returnf boolean track_clicks_text Skait�t vai neskait�t klik��us plain v�stul�
     * @returnf boolean track_clicks_html Skait�t vai neskait�t klik��us HTML v�stul�
     * @returnf boolean track_opens Skait�t vai neskait�t atv�r�anu
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
     * @param string $cid Kampa�as id. To var atrast ar campaigns()
     * @return array Mas�vs, kas satur kampa�as statistiku
     * @returnf integer hard_bounces Nepieg�d�to/nepareizo epastu skaits
     * @returnf integer soft_bounces Pagaidu nepieg�d�to 
     * @returnf integer unsubscribes Epastu skaits, kas atrakst�j�s no kampa�as
     * @returnf integer forwards Skaits, cik reizes v�stule ir p�rs�t�ta
     * @returnf integer opens Skaits, cik reizes atv�rts
     * @returnf date last_open Datums, kad p�d�jo reizi atv�rts
     * @returnf integer unique_opens Unik�lo atv�r�anu skait
     * @returnf integer clicks Skaits, cik daudz ir spiests uz linkiem
     * @returnf integer unique_clicks Unik�lie klik��i uz sait�m
     * @returnf date last_click Datums, kad p�d�jo reizi spiests uz linkiem
     * @returnf integer users_who_clicked Lietot�ju skaits, kas spiedu�i uz sait�m
     * @returnf integer emails_sent Kop�jais skaits, cik v�stules ir izs�t�tas
     */
    function campaignStats($cid) {
        $params = array();
        $params["cid"] = $cid;
        return $this->callServer("campaignStats", $params);
    }

    /**
     * Atrodam kampa�as visus linkus
     *
     * @example mgapi_campaignClickStats.php
     * @example xml-rpc_campaignClickStats.php
     *
     * @param string $cid Kampa�as id. To var atrast ar campaigns()
     * @return struct urls Sai�u mas�vs, kur atsl�ga ir saite
     * @returnf integer clicks Kop�jais klik��u skaits
     * @returnf integer unique Unik�lo klik��u skaits
     */
    function campaignClickStats($cid) {
        $params = array();
        $params["cid"] = $cid;
        return $this->callServer("campaignClickStats", $params);
    }

    /**
     * Atrodam ��s kampa�as epastu dom�nu statistiku
     *
     * @example mgapi_campaignEmailDomainPerformance.php
     * @example xml-rpc_campaignEmailDomainPerformance.php
     *
     * @param string $cid Kampa�as id. To var atrast ar campaigns()
     * @return array Mas�vs ar epasta dom�niem
     * @returnf string domain Dom�na v�rds
     * @returnf integer total_sent Kop� nos�t�to epastu skaits kampa�ai (visi epasti)
     * @returnf integer emails Uz �o dom�nu nos�t�to epstu skaits
     * @returnf integer bounces Neaizg�ju�o epastu skaits
     * @returnf integer opens Unik�lo atv�r�anu skaits
     * @returnf integer clicks Unik�lo klik��u skaits
     * @returnf integer unsubs Skaits, cik atrakst�ju�ies
     * @returnf integer delivered Pieg�d�to v�stu�u skaits
     * @returnf integer emails_pct Skaits, cik epastu procentu�li ir ar �o dom�nu
     * @returnf integer bounces_pct Skaits, cik procentu�li no kop�ja skaita nav pieg�d�ts ar �o dom�nu
     * @returnf integer opens_pct Skaits, cik procentu�li ir atv�rts ar �o dom�nu
     * @returnf integer clicks_pct Skaits, cik procentu�li no �� dom�na ir spiedu�i
     * @returnf integer unsubs_pct Procentu�li, cik daudz no �� dom�na ir atrakst�ju�ies
     */
    function campaignEmailDomainPerformance($cid) {
        $params = array();
        $params["cid"] = $cid;
        return $this->callServer("campaignEmailDomainPerformance", $params);
    }

    /**
     * Atrodam neeksist�jo�os/nepareizos epastus (hard bounces)
     *
     * @example mgapi_campaignHardBounces.php
     * @example xml-rpc_campaignHardBounces.php
     *
     * @param string $cid Kampa�as id. To var atrast ar campaigns()
     * @param integer $start Nav oblig�ts. Lapa, no kuras izvad�t datus. P�c noklus�juma ir 0, kas atbilst pirmajai lapai
     * @param integer $limit Nav oblig�ts. Rezult�tu skaits lap�. P�c noklus�juma 1000. Maksim�l� v�rt�ba ir 15000
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
     * @param string $cid Kampa�as id. To var atrast ar campaigns()
     * @param integer $start Nav oblig�ts. Lapa, no kuras izvad�t datus. P�c noklus�juma ir 0, kas atbilst pirmajai lapai
     * @param integer $limit Nav oblig�ts. Rezult�tu skaits lap�. P�c noklus�juma 1000. Maksim�l� v�rt�ba ir 15000
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
     * Atrodam visus e-pastus, kas ir atrakst�ju�ies no ��s kampa�as
     *
     * @example mgapi_campaignUnsubscribes.php
     * @example xml-rpc_campaignUnsubscribes.php
     *
     * @param string $cid Kampa�as id. To var atrast ar campaigns()
     * @param integer $start Nav oblig�ts. Lapa, no kuras izvad�t datus. P�c noklus�juma ir 0, kas atbilst pirmajai lapai
     * @param integer $limit Nav oblig�ts. Rezult�tu skaits lap�. P�c noklus�juma 1000. Maksim�l� v�rt�ba ir 15000
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
     * Atgrie� valstu sarakstu, no kur�m ir atv�rtas v�stules un cik daudz
     *
     * @example mgapi_campaignGeoOpens.php
     * @example xml-rpc_campaignGeoOpens.php
     *
     * @param string $cid Kampa�as id. To var atrast ar campaigns()
     * @return array countries Mas�vs ar valstu sarakstu
     * @returnf string code Valsts kods ISO3166 form�t�, satur 2 simbolus
     * @returnf string name Valsts nosaukums
     * @returnf int opens Skaits, cik daudz atv�rts
     */
    function campaignGeoOpens($cid) {
        $params = array();
        $params["cid"] = $cid;
        return $this->callServer("campaignGeoOpens", $params);
    }

    /**
     * Atrodam p�rs�t��anas statistiku
     *
     * @example mgapi_campaignForwardStats.php
     * @example xml-rpc_campaignForwardStats.php
     *
     * @param string $cid Kampa�as id. To var atrast ar campaigns()
     * @param integer $start Nav oblig�ts. Lapa, no kuras izvad�t datus. P�c noklus�juma ir 0, kas atbilst pirmajai lapai
     * @param integer $limit Nav oblig�ts. Rezult�tu skaits lap�. P�c noklus�juma 1000. Maksim�l� v�rt�ba ir 15000
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
     * Atgrie� kampa�as atmesto v�stu�u tekstus, kuras nav vec�kas par 30 dien�m
     *
     * @example mgapi_campaignBounceMessages.php
     * @example xml-rpc_campaignBounceMessages.php
     *
     * @param string $cid Kampa�as id. To var atrast ar campaigns()
     * @param integer $start Nav oblig�ts. Lapa, no kuras izvad�t datus. P�c noklus�juma ir 0, kas atbilst pirmajai lapai
     * @param integer $limit Nav oblig�ts. Rezult�tu skaits lap�. P�c noklus�juma 25. Maksim�l� v�rt�ba ir 50
     * @return array bounces Mas�vs, kas satur atsviesto epastu saturu
     * @returnf string date Laiks, kad v�stule sa�emta
     * @returnf string email Epasta arese, uz kuru neizdev�s nos�t�t
     * @returnf string message Atsviest�s v�stules saturs
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
     * @param array $options Mas�vs ar kampa�as parametriem
      string permission_reminder Atg�din�jums lietot�jiem, k� tie nok�uva sarakst�
      string notify_to Epasta adrese, uz kuru s�t�t pazi�ojumus
      bool subscription_notify S�t�t pazi�ojumus par to, ka ir jauns lietot�js pierakst�jies
      bool unsubscription_notify S�t�t pazi�ojumus par to, ka ir jauns lietot�js atrakst�jies
      bool has_email_type_option Ļaut izv�l�ties epasta form�tu

     *
     * @return string Atgrie� jaun� saraksta ID
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
     * @param string $name Parametra nosaukums (skat�ties pie listCreate() options lauku ). Iesp�jamie parametri: title, permission_reminder, notify_to, utt.
     * @return boolean true, ja ir veiksm�gi, pret�j� gad�jum� atgrie� k��das pazi�ojumu
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
     * @return array Mas�vs ar sarakstiem
     * @returnf string id Saraksta id. �� v�rt�ba tiek izmantota c�t�s funkcij�s, kas str�d� ar sarakstiem.
     * @returnf integer web_id Saraksta id, kas tiek izmantots web administr�cijas lap�
     * @returnf string name Saraksta nosaukums
     * @returnf date date_created Saraksta izveido�anas datums.
     * @returnf integer member_count Lietot�ju skaits sarakst�
     * @returnf integer unsubscribe_count Lietot�ju skaits, cik atrakst�ju�ies no saraksta
     * @returnf string default_from_name Noklus�juma v�rt�ba From Name priek� kampa��m, kas izmanto �o sarakstu
     * @returnf string default_from_email Noklus�juma v�rt�ba From Email priek� kampa��m, kas izmanto �o sarakstu
     * @returnf string default_subject Noklus�juma v�rt�ba Subject priek� kampa��m, kas izmanto �o sarakstu
     * @returnf string default_language Noklus�ja valoda saraksta form�m
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
     * @returnf bool req Vai �is lauks ir oblig�ti aizpild�ms (true) vai n� (false)
     * @returnf string field_type Merge tada datu tips. Ir pie�aujamas ��das v�rt�bas: email, text, number, date, address, phone, website, image
     * @returnf bool show Nor�da, vai r�d�t �o lauku lietot�ju sarakst�.
     * @returnf string order K�rtas numurs
     * @returnf string default V�rt�ba p�c noklus�juma
     * @returnf string tag Merge tags, kas tiek izmantots form�s, listSubscribe() un listUpdateMember()
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
     * @param string $tag Merge tags, kuru vajag pievienot, piem�ram, FNAME
     * @param string $name Gar�ks nosaukum, kas tiks r�d�ts lietot�jiem
     * @param array $options Nav oblig�ts. Da��di parametri merge tagam.
      string field_type Nav oblig�ts. K�da v�rt�ba no: text, number, date, address, phone, website, image. P�c noklus�juma ir text
      boolean req Nav oblig�ts. Nor�da, vai lauks ir oblig�ti aizpild�ms. P�c noklus�juma, false
      boolean show Nav oblig�ts. Nor�da, vai r�d�t �o lauku lietot�ju sarakst�. P�c noklus�juma, true
      string default_value Nav oblig�ts. V�rt�ba p�c noklus�juma

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
     * Atjaunojam merge taga parametrus sarakst�. Merge taga tipu nevar nomain�t
     *
     * @example mgapi_listMergeVarUpdate.php
     * @example xml-rpc_listMergeVarUpdate.php
     *
     * @param string $id Saraksta ID. Saraksta ID var atrast ar lists() metodi
     * @param string $tag Merge tags, kuru vajag atjaunot
     * @param array $options Parametri merge taga atjauno�anai. Pareizus parametrus skat�ties pie metodes listMergeVarAdd()
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
     * Tiek izdz�sts merge tags no saraksta un v�rt�ba visiem saraksta lietot�jiem. Dati tie izdz�sti neatgriezeniski
     *
     * @example mgapi_listMergeVarDel.php
     * @example xml-rpc_listMergeVarDel.php
     *
     * @param string $id Saraksta ID. Saraksta ID var atrast ar lists() metodi
     * @param string $tag Merge tags, kuru vajag izdz�st
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
     * @param boolean $update_existing Vai atjaunot eksistejo�os epastus. Pec noklusejuma false (atgriezis kludas pazinojumu)
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
     * @param string $phone T�lrunis, ko japievieno sarakstam
     * @param array $merge_vars Masivs, kas satur MERGE lauku vertibas (FNAME, LNAME, etc.) Maksimalais izmers 255
     * @param boolean $update_existing Vai atjaunot eksistejo�os epastus. Pec noklusejuma false (atgriezis kludas pazinojumu)

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
     * @param array $batch Masivs, kas satur epastu datus. Epasta dati ir masivs ar �ada atslegam: "EMAIL" epasta adresei, "EMAIL_TYPE" epasta tips (html vai plain) 
     * @param boolean $double_optin Vai sutit apstiprinajuma vestuli. Pec noklusejuma true
     * @param boolean $update_existing Vai atjaunot eksistejo�os epastus. Pec noklusejuma false (atgriezis kludas pazinojumu)
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
     * @param array $batch Masivs, kas satur epastu datus. Epasta dati ir masivs ar �ada atslegam: "SMS" epasta adresei
     * @param boolean $update_existing Vai atjaunot eksistejo�os epastus. Pec noklusejuma false (atgriezis kludas pazinojumu)
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
     * @param array $phones T�lru�u masivs
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
     * @param integer $start Nav obligats. Nepiecie�ams lielam sarakstam. Lapas numurs, no kuras sakt. Pirmajai lapai atbilst numurs 0
     * @param integer $limit Nav obligats. Nepiecie�ams lielam sarakstam. Skaits, cik daudz atgriezt epastus. Pec noklusejuma 100, maksimalais ir 15000
     * @return array Masivs ar lietotaju sarakstu
     * @returnf string email Lietotaja epasts
     * @returnf date timestamp Peivieno�anas datums
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
     * Saraksta pieauguma informacija pa mene�iem
     *
     * @example mgapi_listGrowthHistory.php
     * @example xml-rpc_listGrowthHistory.php
     *
     * @param string $id Saraksta ID. Saraksta ID var atrast ar lists() metodi
     * @return array Masivs pa mene�iem
     * @returnf string month Gads un menesis YYYY-MM formata
     * @returnf integer existing Skaits, cik bija lietotaju mene�a sakuma
     * @returnf integer imports Skaits, cik daudz teko�aja menesi tika pievienoti lietotaji
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
     * @return array Mas�vs ar saraksta segmentiem
     * @returnf string id Saraksta segmenta id.
     * @returnf integer web_id Saraksta segmenta id, kas tiek izmantots web administr�cijas lap�
     * @returnf string name Saraksta segmenta nosaukums
     * @returnf date date_created Saraksta izveido�anas datums.
     * @returnf integer member_count Lietot�ju skaits sarakst�
     */
    function listSegments($id) {
        $params = array();
        $params["id"] = $id;
        return $this->callServer("listSegments", $params);
    }

    /**
     * Atgrie� da�adu informaciju par lietotaju kontu
     *
     * @example mgapi_getAccountDetails.php
     * @example xml-rpc_getAccountDetails.php
     *
     * @return array Masivs, kas satur da�adu informaciju par �is API atlsegas lietotaja kontu
     * @returnf string user_id Lietotaja unikalais ID, tas tiek izmantots buvejot da�adas saites
     * @returnf string username Lietotaja lietotajvards
     * @returnf bool is_trial Vai lietotajs ir trial
     * @returnf int emails_left Skaits, cik daudz epastus var nosutit
     * @returnf datetime first_payment Pirma maksajuma datums
     * @returnf datetime last_payment Pedeja maksajuma datums
     * @returnf int times_logged_in Skaits, cik daudz reizes lietotajs caur web ir ielogojies
     * @returnf datetime last_login Datums, kad pedejo reizi bija ielogojies caur web
     * @returnf array contact Masivs, kas satur kontkatinformaciju: Vards, uzvards, epasts, uznemuma nosaukums, adrese, majas lapas adrese, telefons, fakss
     * @returnf array orders Masivs, kas satur informaciju par samaksatajiem rekiniem: rekina numurs, plans, cena, valuta, izraksti�anas datums, pakas deriguma termin�
     */
    function getAccountDetails() {
        $params = array();
        return $this->callServer("getAccountDetails", $params);
    }

    /**
     * Atrodam visu sarakstu ID, kuros ir �is epasts
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
     * @returnf string apikey �o atslegu var izmantot, lai pieslegtos API
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
     * @return string atgrie� jaunu API atslegu
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
     * "ping" - vienkar� veids, ka parbaudit, vai viss ir kartiba. Ja ir kadas problemas, tiks atgriezts par to pazinojums.
     *
     * @example mgapi_ping.php
     * @example xml-rpc_ping.php
     *
     * @return string tiek atgriezts teksts "Everything's Ok!", ja viss ir kartiba, ja nav, tad atgrie� kludas pazinojumu
     */
    function ping() {
        $params = array();
        return $this->callServer("ping", $params);
    }

    /**
     * Piesledzas pie servera uz izsauc nepiecie�amo metodi un atgrie� rezultatu
     * �o funkciju nav nepiecie�ams izsaukt manuali
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