<?php

require_once dirname(__DIR__, 2) . '/bootstrap/smoke.php';
require_once dirname(__DIR__, 2) . '/adapters/f3cms/bootstrap.php';
require_once dirname(__DIR__, 2) . '/adapters/f3cms/press.php';

tests_smoke_run(function () {
    $pressId = isset($GLOBALS['argv'][1]) ? (int) $GLOBALS['argv'][1] : 910904;

    tests_f3cms_seed_press_row($pressId, \F3CMS\fPress::ST_DRAFT);

    $currentPress = \F3CMS\fPress::one($pressId);
    if (empty($currentPress)) {
        throw new \RuntimeException('Press seed row not found.');
    }

    $rollbackTriggered = false;

    try {
        mh()->begin();

        $req = [
            'id' => $pressId,
            'status' => \F3CMS\fPress::ST_PUBLISHED,
        ];

        $workflowTransition = \F3CMS\rPress::applyWorkflowPublishedTransition($currentPress, $req, 1);
        $req = $workflowTransition['req'];

        mh()->insert('tbl_press_log', [
            'parent_id' => $workflowTransition['trace']['parent_id'],
            'action_code' => $workflowTransition['trace']['action_code'],
            'old_state_code' => $workflowTransition['trace']['old_state_code'],
            'new_state_code' => $workflowTransition['trace']['new_state_code'],
            'insert_user' => $workflowTransition['trace']['insert_user'],
        ]);

        // Simulate a downstream write failure after workflow trace has already been written.
        mh()->delete('tbl_press', ['id' => $pressId]);

        $published = \F3CMS\fPress::published($req);
        if (empty($published)) {
            throw new \RuntimeException('Press status update failed.');
        }

        mh()->commit();
    } catch (\Throwable $e) {
        $rollbackTriggered = true;
        mh()->rollback();
    }

    $pressRow = mh()->get('tbl_press', ['id', 'status'], ['id' => $pressId]);
    $traceCount = (int) mh()->count('tbl_press_log', ['parent_id' => $pressId]);

    if (true !== $rollbackTriggered) {
        throw new \RuntimeException('Rollback was not triggered.');
    }

    if (empty($pressRow) || \F3CMS\fPress::ST_DRAFT !== $pressRow['status']) {
        throw new \RuntimeException('Press row was not restored to Draft after rollback.');
    }

    if (0 !== $traceCount) {
        throw new \RuntimeException('Press trace should not persist after rollback.');
    }

    return [
        'rollback_triggered' => $rollbackTriggered,
        'press_status_after_rollback' => empty($pressRow) ? null : $pressRow['status'],
        'trace_count_after_rollback' => $traceCount,
    ];
}, 'tests_bootstrap_f3cms');