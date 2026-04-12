<?php

require_once dirname(__DIR__, 2) . '/bootstrap/smoke.php';
require_once dirname(__DIR__, 2) . '/adapters/f3cms/bootstrap.php';

tests_smoke_run(function () {
    $payload = tests_smoke_load_json_fixture('event_rule_engine/member_register_press_seen_claim.json');
    $slug = 'event-rule-member-register-duty-' . uniqid();
    $dutyId = 0;

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
        $loaded = \F3CMS\kDuty::loadRulePayload($dutyId, 'claim');

        if ('Member::Register' !== ($loaded['trigger'] ?? null)) {
            throw new \RuntimeException('Expected Member::Register trigger in duty claim payload.');
        }

        if ('press-103-seen-reward' !== ($loaded['task_template']['slug'] ?? null)) {
            throw new \RuntimeException('Expected task_template.slug to be preserved in duty claim payload.');
        }

        if ('MEMBER_SEEN_TARGET' !== ($loaded['task_template']['factor']['rules'][0]['type'] ?? null)) {
            throw new \RuntimeException('Expected MEMBER_SEEN_TARGET rule in task_template.factor.');
        }

        if (100 !== (int) ($loaded['task_template']['reward']['amount'] ?? 0)) {
            throw new \RuntimeException('Expected reward amount to be preserved in duty claim payload.');
        }

        $row = \F3CMS\fDuty::oneBySlug($slug);
        if ((int) ($row['id'] ?? 0) !== $dutyId) {
            throw new \RuntimeException('Expected inserted duty definition to be queryable by slug.');
        }

        return [
            'duty_id' => $dutyId,
            'slug' => $slug,
            'trigger' => $loaded['trigger'],
            'task_template' => $loaded['task_template'],
        ];
    } finally {
        if ($dutyId > 0) {
            mh()->delete('tbl_duty', ['id' => $dutyId]);
        }
    }
}, 'tests_bootstrap_f3cms');