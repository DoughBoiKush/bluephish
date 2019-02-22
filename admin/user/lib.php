<?php

require_once($CFG->dirroot.'/user/filters/lib.php');

if (!defined('MAX_BULK_USERS')) {
    define('MAX_BULK_USERS', 2000);
}

function add_selection_all($ufiltering) {
    global $SESSION, $DB, $CFG, $USER;
     if(!is_siteadmin())
    {
$avusers='';
$getcohort1=$DB->get_records_sql("SELECT * from bm_cohort_members where userid=$USER->id");
                    foreach ($getcohort1 as $getco1) {
                        $cohortidd=$getco1->cohortid;
                    }
                    if(!empty($cohortidd))
                    {
                      $query2="SELECT bm_user.id as users, bm_user.firstname as firstname, bm_user.lastname as lastname FROM bm_user
                    INNER JOIN bm_cohort_members ON bm_user.id = bm_cohort_members.userid
                    INNER JOIN bm_cohort ON bm_cohort_members.cohortid = bm_cohort.id
                    WHERE bm_user.id NOT IN (select userid from bm_role_assignments where roleid='10') and bm_cohort.id=$cohortidd and bm_user.id!=2";

                      $data2=$DB->get_records_sql($query2);
                      foreach ($data2 as $key2) 
                      {

                        $avusers.=$key2->users.',';
                      }
                  }
                  $avusers=rtrim($avusers, ',');
}
if(is_siteadmin())
{

    list($sqlwhere, $params) = $ufiltering->get_sql_filter("id<>:exguest AND deleted <> 1", array('exguest'=>$CFG->siteguest));

    $rs = $DB->get_recordset_select('user', $sqlwhere, $params, 'fullname', 'id,'.$DB->sql_fullname().' AS fullname');
}else
{
    if(!empty($avusers))
    {
        list($sqlwhere, $params) = $ufiltering->get_sql_filter("id<>:exguest AND deleted <> 1 AND id IN ($avusers)", array('exguest'=>$CFG->siteguest));

        $rs = $DB->get_recordset_select('user', $sqlwhere, $params, 'fullname', 'id,'.$DB->sql_fullname().' AS fullname');
    }else
    {

        $avusers=0;
        list($sqlwhere, $params) = $ufiltering->get_sql_filter("id<>:exguest AND deleted <> 1 AND id IN ($avusers)", array('exguest'=>$CFG->siteguest));

        $rs = $DB->get_recordset_select('user', $sqlwhere, $params, 'fullname', 'id,'.$DB->sql_fullname().' AS fullname');
    }
}
    foreach ($rs as $user) {
        if (!isset($SESSION->bulk_users[$user->id])) {
            $SESSION->bulk_users[$user->id] = $user->id;
        }
    }
    $rs->close();
}

function get_selection_data($ufiltering) {
    global $SESSION, $DB, $CFG, $USER;
    if(!is_siteadmin())
    {
$avusers='';
$getcohort1=$DB->get_records_sql("SELECT * from bm_cohort_members where userid=$USER->id");
                    foreach ($getcohort1 as $getco1) {
                        $cohortidd=$getco1->cohortid;
                    }
                    if(!empty($cohortidd))
                    {
                      $query2="SELECT bm_user.id as users, bm_user.firstname as firstname, bm_user.lastname as lastname FROM bm_user
                    INNER JOIN bm_cohort_members ON bm_user.id = bm_cohort_members.userid
                    INNER JOIN bm_cohort ON bm_cohort_members.cohortid = bm_cohort.id
                    WHERE bm_user.id NOT IN (select userid from bm_role_assignments where roleid='10') and bm_cohort.id=$cohortidd and bm_user.id!=2";
                      
                      //$query1="SELECT count(*) as users FROM `bm_user` where deleted=0 and id NOT IN(1,2)";
                      $data2=$DB->get_records_sql($query2);
                      foreach ($data2 as $key2) 
                      {

                        $avusers.=$key2->users.',';
                      }
                  }
                  $avusers=rtrim($avusers, ',');
}
    // get the SQL filter
if(is_siteadmin())
    {

        list($sqlwhere, $params) = $ufiltering->get_sql_filter("id<>:exguest AND deleted <> 1", array('exguest'=>$CFG->siteguest));

        $total  = $DB->count_records_select('user', "id<>:exguest AND deleted <> 1", array('exguest'=>$CFG->siteguest));

    }else
    {
        if(!empty($avusers))
        {

            list($sqlwhere, $params) = $ufiltering->get_sql_filter("id<>:exguest AND deleted <> 1 AND id IN ($avusers)", array('exguest'=>$CFG->siteguest));

            $total  = $DB->count_records_select('user', "id<>:exguest AND deleted <> 1 AND id IN ($avusers)", array('exguest'=>$CFG->siteguest));

        }else
        {

            $avusers=0;
            list($sqlwhere, $params) = $ufiltering->get_sql_filter("id<>:exguest AND deleted <> 1 AND id IN ($avusers)", array('exguest'=>$CFG->siteguest));

            $total  = $DB->count_records_select('user', "id<>:exguest AND deleted <> 1 AND id IN ($avusers)", array('exguest'=>$CFG->siteguest));

        }
    }
    
    $acount = $DB->count_records_select('user', $sqlwhere, $params);
    $scount = count($SESSION->bulk_users);

    $userlist = array('acount'=>$acount, 'scount'=>$scount, 'ausers'=>false, 'susers'=>false, 'total'=>$total);
    $userlist['ausers'] = $DB->get_records_select_menu('user', $sqlwhere, $params, 'fullname', 'id,'.$DB->sql_fullname().' AS fullname', 0, MAX_BULK_USERS);

    if ($scount) {
        if ($scount < MAX_BULK_USERS) {
            $bulkusers = $SESSION->bulk_users;
        } else {
            $bulkusers = array_slice($SESSION->bulk_users, 0, MAX_BULK_USERS, true);
        }
        list($in, $inparams) = $DB->get_in_or_equal($bulkusers);
        $userlist['susers'] = $DB->get_records_select_menu('user', "id $in", $inparams, 'fullname', 'id,'.$DB->sql_fullname().' AS fullname');
    }

    return $userlist;
}
