<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SiteConfig extends Model
{
  protected $fillable = [
    'site_name', 'allow_mitm', 'allow_hide_icon', 'allow_weak_cryptographic_api',
    'allow_vulnerable_leak', 'allow_malicious_leak', 'max_no_rules_broken',
    'max_no_flow', 'allow_cert_pinning_mitm', 'api_key', 'password',
    'privilege_escalation', 'vulnerability_count', 'warning_count',
    'information_count'
  ];

  public static function getSiteName() {

    return static::select('site_name', 'created_at')->orderBy('created_at', 'desc')
    ->first()->site_name;

  }

  public static function isAppVisible($result) {

    $criteria = static::select('site_name', 'max_cvss',
    'allow_mitm', 'allow_hide_icon',
    'allow_weak_cryptographic_api', 'allow_vulnerable_leak',
    'allow_malicious_leak', 'max_no_rules_broken',
    'max_no_flow', 'allow_cert_pinning_mitm', 'allow_api_key', 'allow_password',
    'allow_privilege_escalation', 'max_vulnerability_count', 'created_at')
    ->orderBy('created_at', 'desc')
    ->first();

    if ( (!$criteria->allow_mitm) && ($result->mitm) ) {
      return false;
    }

    if ( (!$criteria->allow_hide_icon) && ($result->hide_icon) ) {
      return false;
    }

    if ( (!$criteria->allow_weak_cryptographic_api) && ($result->weak_crypto) ) {
      return false;
    }

    if ( (!$criteria->allow_vulnerable_leak) && ($result->vulnerable_leak) ) {
      return false;
    }

    if ( (!$criteria->allow_malicious_leak) && ($result->malicious_leak) ) {
      return false;
    }

    if( ($criteria->max_no_rules_broken != -1) && ($criteria->max_no_rules_broken < $result->no_rules_broken) ) {
      return false;
    }

    if( ($criteria->max_no_flow != -1) && ($criteria->max_no_flow < $result->no_flow) ) {
      return false;
    }

    if ( (!$criteria->allow_cert_pinning_mitm) && ($result->cert_pinning_mitm) ) {
      return false;
    }

    if ( (!$criteria->allow_api_key) && ($result->api_key) ) {
      return false;
    }

    if ( (!$criteria->allow_password) && ($result->password) ) {
      return false;
    }

    if ( (!$criteria->allow_privilege_escalation) && ($result->privilege_escalation) ) {
      return false;
    }

    if( ($criteria->max_vulnerability_count != -1) && ($criteria->max_vulnerability_count < $result->vulnerability_count) ) {
      return false;
    }

    if( (intval($criteria->max_cvss) != -1) &&
        ($criteria->max_cvss < $result->mitm_cvss) &&
        ($criteria->max_cvss < $result->cert_pinning_mitm_cvss) &&
        ($criteria->max_cvss < $result->privilege_escalation_cvss) ) {
      return false;
    }

    return true;
  }
}
