<?php

require_once dirname(__DIR__, 2) . '/bootstrap/smoke.php';
require_once dirname(__DIR__, 2) . '/adapters/f3cms/bootstrap.php';

tests_smoke_run(function () {
    $payload = tests_smoke_load_json_fixture('event_rule_engine/basic_or_rule.json');
    $memberName = 'event-rule-eval-member-' . uniqid();
    $heraldrySlug = 'badge-duty-eval-' . uniqid();
    $dutySlug = 'event-rule-duty-eval-' . uniqid();
    $memberId = 0;
    $heraldryId = 0;
    $relationId = 0;
    $accountId = 0;
    $dutyId = 0;

    $payload['rules'][1]['target'] = $heraldrySlug;

    mh()->insert('tbl_member', [
        'display_name' => $memberName,
        'status' => 'Enabled',
        'insert_ts' => date('Y-m-d H:i:s'),
        'insert_user' => 1,
        'last_ts' => date('Y-m-d H:i:s'),
        'last_user' => 1,
    ]);
    $memberId = (int) mh()->id();

    mh()->insert('tbl_heraldry', [
        'slug' => $heraldrySlug,
        'status' => 'Enabled',
        'insert_ts' => date('Y-m-d H:i:s'),
        'insert_user' => 1,
        'last_ts' => date('Y-m-d H:i:s'),
        'last_user' => 1,
    ]);
    $heraldryId = (int) mh()->id();

    mh()->insert('tbl_member_heraldry', [
        'member_id' => $memberId,
        'heraldry_id' => $heraldryId,
        'insert_ts' => date('Y-m-d H:i:s'),
        'insert_user' => 1,
    ]);
    $relationId = (int) mh()->id();

    mh()->insert('tbl_manaccount', [
        'member_id' => $memberId,
        'balance' => 500,
        'status' => 'Enabled',
        'insert_ts' => date('Y-m-d H:i:s'),
        'insert_user' => 1,
        'last_ts' => date('Y-m-d H:i:s'),
        'last_user' => 1,
    ]);
    $accountId = (int) mh()->id();

    mh()->insert('tbl_duty', [
        'slug' => $dutySlug,
        'claim' => json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
        'factor' => null,
        'next' => null,
        'status' => 'Enabled',
        'insert_ts' => date('Y-m-d H:i:s'),
        'insert_user' => 1,
        'last_ts' => date('Y-m-d H:i:s'),
        'last_user' => 1,
    ]);
    $dutyId = (int) mh()->id();

    try {
        $result = \F3CMS\kDuty::evaluateForMember($dutyId, $memberId, 'claim', [
            'watched_video_codes' => [],
            'exam_scores' => ['default' => 50],
        ])->toArray();

        if (true !== $result['matched'] || 'matched' !== $result['result_type']) {
            throw new \RuntimeException('Expected matched result from duty/member evaluation helper path.');
        }

        return [
            'member_id' => $memberId,
            'duty_id' => $dutyId,
            'result' => $result,
        ];
    } finally {
        if ($dutyId > 0) {
            mh()->delete('tbl_duty', ['id' => $dutyId]);
        }
        if ($accountId > 0) {
            mh()->delete('tbl_manaccount', ['id' => $accountId]);
        }
        if ($relationId > 0) {
            mh()->delete('tbl_member_heraldry', ['id' => $relationId]);
        }
        if ($heraldryId > 0) {
            mh()->delete('tbl_heraldry', ['id' => $heraldryId]);
        }
        if ($memberId > 0) {
            mh()->delete('tbl_member', ['id' => $memberId]);
        }
    }
}, 'tests_bootstrap_f3cms');