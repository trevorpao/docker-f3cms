<?php

require_once dirname(__DIR__, 2) . '/bootstrap/smoke.php';
require_once dirname(__DIR__, 2) . '/adapters/f3cms/bootstrap.php';
require_once dirname(__DIR__, 2) . '/adapters/f3cms/workflow_engine_runtime.php';

tests_smoke_run(function () {
    $workflowCode = 'SJSE_EDGE';
    $workflowVersion = 1;

    $engine = tests_f3cms_load_workflow_engine($workflowCode, $workflowVersion);
    $runtimeContext = [];

    $path = [];
    $summaryFields = ['available_action_codes', 'join_pending', 'join_required_count', 'join_completed_count'];
    $path[] = tests_f3cms_workflow_transition_summary(
        tests_f3cms_workflow_transit_runtime($engine, $runtimeContext, 'SUBMIT', 101, 'ROLE_AUTHOR'),
        $summaryFields
    );
    $path[] = tests_f3cms_workflow_transition_summary(
        tests_f3cms_workflow_transit_runtime($engine, $runtimeContext, 'FORMAT_PASS', 201, 'ROLE_EDITOR'),
        $summaryFields
    );
    $path[] = tests_f3cms_workflow_transition_summary(
        tests_f3cms_workflow_transit_runtime($engine, $runtimeContext, 'FIRST_PASS', 301, 'ROLE_REVIEWER'),
        $summaryFields
    );

    $duplicateMessage = null;
    try {
        tests_f3cms_workflow_transit_runtime($engine, $runtimeContext, 'FIRST_PASS', 301, 'ROLE_REVIEWER');
    } catch (\Throwable $e) {
        $duplicateMessage = $e->getMessage();
    }

    $path[] = tests_f3cms_workflow_transition_summary(
        tests_f3cms_workflow_transit_runtime($engine, $runtimeContext, 'FIRST_PASS', 302, 'ROLE_REVIEWER'),
        $summaryFields
    );

    $finalProjection = $engine->project($runtimeContext);
    $traceRows = $runtimeContext['trace_rows'];

    if (true !== $path[2]['join_pending']) {
        throw new \RuntimeException('First reviewer should leave FIRST_REVIEW in join-pending state.');
    }

    if ('FIRST_REVIEW' !== $path[2]['current_stage_codes'][0]) {
        throw new \RuntimeException('First reviewer should keep current stage at FIRST_REVIEW.');
    }

    if ('InFirstReview' !== $path[2]['current_state_code']) {
        throw new \RuntimeException('First reviewer should keep current state at InFirstReview.');
    }

    if (null === $duplicateMessage || false === strpos($duplicateMessage, 'already participated')) {
        throw new \RuntimeException('Duplicate reviewer should be blocked in current parallel stage.');
    }

    if (false !== $path[3]['join_pending']) {
        throw new \RuntimeException('Second reviewer should complete the parallel join.');
    }

    if ('EXEC_DECISION' !== $path[3]['current_stage_codes'][0]) {
        throw new \RuntimeException('Second reviewer should advance to EXEC_DECISION.');
    }

    if ('FirstPassed' !== $path[3]['current_state_code']) {
        throw new \RuntimeException('Second reviewer should advance state to FirstPassed.');
    }

    if ('EXEC_DECISION' !== $finalProjection['current_stage_codes'][0]) {
        throw new \RuntimeException('Final projection should expose EXEC_DECISION stage.');
    }

    return [
        'workflow_code' => $workflowCode,
        'path' => $path,
        'duplicate_message' => $duplicateMessage,
        'final_projection' => $finalProjection,
        'trace_count' => count($traceRows),
        'trace_rows' => $traceRows,
    ];
}, 'tests_bootstrap_f3cms');