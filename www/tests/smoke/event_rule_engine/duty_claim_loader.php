<?php

require_once dirname(__DIR__, 2) . '/bootstrap/smoke.php';
require_once dirname(__DIR__, 2) . '/adapters/f3cms/bootstrap.php';

tests_smoke_run(function () {
    $payload = tests_smoke_load_json_fixture('event_rule_engine/basic_or_rule.json');
    $slug = 'event-rule-duty-' . uniqid();

    mh()->insert('tbl_duty', [
        'slug' => $slug,
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
        $engine = \F3CMS\kDuty::createRuleEngine($dutyId, 'claim');
        $engine->validatePayload();

        $matched = $engine->evaluate([
            'member_id' => 101,
            'watched_video_codes' => ['vid_001'],
            'exam_scores' => ['default' => 85],
            'heraldry_codes' => [],
            'account_balance' => 100,
            'account_status' => 'ACTIVE',
        ])->toArray();

        if (true !== $matched['matched'] || 'matched' !== $matched['result_type']) {
            throw new \RuntimeException('Expected matched result from duty claim loader path.');
        }

        return [
            'duty_id' => $dutyId,
            'slug' => $slug,
            'payload_column' => 'claim',
            'result' => $matched,
        ];
    } finally {
        if ($dutyId > 0) {
            mh()->delete('tbl_duty', ['id' => $dutyId]);
        }
    }
}, 'tests_bootstrap_f3cms');