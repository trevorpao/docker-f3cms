<?php

require_once dirname(__DIR__, 2) . '/bootstrap/smoke.php';
require_once dirname(__DIR__, 2) . '/adapters/f3cms/bootstrap.php';
require_once dirname(__DIR__, 2) . '/adapters/f3cms/workflow_engine_runtime.php';

tests_smoke_run(function () {
    $workflowCode = 'SJSE_EDGE';
    $workflowVersion = 1;

    $engine = tests_f3cms_load_workflow_engine($workflowCode, $workflowVersion);

    $summaryFields = ['available_action_codes'];

    $revisionPath = [];
    $revisionRuntime = [];
    $revisionPath[] = tests_f3cms_workflow_transition_summary(
        tests_f3cms_workflow_transit_runtime($engine, $revisionRuntime, 'SUBMIT', 101, 'ROLE_AUTHOR'),
        $summaryFields
    );
    $revisionPath[] = tests_f3cms_workflow_transition_summary(
        tests_f3cms_workflow_transit_runtime($engine, $revisionRuntime, 'FORMAT_PASS', 201, 'ROLE_EDITOR'),
        $summaryFields
    );
    $revisionPath[] = tests_f3cms_workflow_transition_summary(
        tests_f3cms_workflow_transit_runtime($engine, $revisionRuntime, 'FIRST_REVISE', 301, 'ROLE_REVIEWER'),
        $summaryFields
    );
    $revisionPath[] = tests_f3cms_workflow_transition_summary(
        tests_f3cms_workflow_transit_runtime($engine, $revisionRuntime, 'REVISION_SUBMIT', 101, 'ROLE_AUTHOR'),
        $summaryFields
    );
    $revisionPath[] = tests_f3cms_workflow_transition_summary(
        tests_f3cms_workflow_transit_runtime($engine, $revisionRuntime, 'ROUTE_TO_THIRD', 201, 'ROLE_EDITOR'),
        $summaryFields
    );

    $revisionTraceRows = $revisionRuntime['trace_rows'];

    $terminatePath = [];
    $terminateRuntime = [];
    $terminatePath[] = tests_f3cms_workflow_transition_summary(
        tests_f3cms_workflow_transit_runtime($engine, $terminateRuntime, 'SUBMIT', 101, 'ROLE_AUTHOR'),
        $summaryFields
    );
    $terminatePath[] = tests_f3cms_workflow_transition_summary(
        tests_f3cms_workflow_transit_runtime($engine, $terminateRuntime, 'FORMAT_PASS', 201, 'ROLE_EDITOR'),
        $summaryFields
    );
    $terminatePath[] = tests_f3cms_workflow_transition_summary(
        tests_f3cms_workflow_transit_runtime($engine, $terminateRuntime, 'FIRST_REVISE', 301, 'ROLE_REVIEWER'),
        $summaryFields
    );
    $terminatePath[] = tests_f3cms_workflow_transition_summary(
        tests_f3cms_workflow_transit_runtime($engine, $terminateRuntime, 'WITHDRAW', 101, 'ROLE_AUTHOR'),
        $summaryFields
    );

    $terminateTraceRows = $terminateRuntime['trace_rows'];

    if ('THIRD_REVIEW' !== end($revisionPath)['current_stage_codes'][0]) {
        throw new \RuntimeException('Revision route should end at THIRD_REVIEW.');
    }

    if ('Withdrawn' !== end($terminatePath)['current_state_code']) {
        throw new \RuntimeException('Terminate route should end at Withdrawn.');
    }

    if ('rollback' !== $revisionTraceRows[2]['transition_kind']) {
        throw new \RuntimeException('FIRST_REVISE should execute as rollback transition.');
    }

    if ('branch' !== $revisionTraceRows[4]['transition_kind']) {
        throw new \RuntimeException('ROUTE_TO_THIRD should execute as branch transition.');
    }

    if ('terminate' !== $terminateTraceRows[3]['transition_kind']) {
        throw new \RuntimeException('WITHDRAW should execute as terminate transition.');
    }

    return [
        'workflow_code' => $workflowCode,
        'revision_route' => [
            'path' => $revisionPath,
            'trace_count' => count($revisionTraceRows),
            'trace_rows' => $revisionTraceRows,
            'final_stage_code' => end($revisionPath)['current_stage_codes'][0],
        ],
        'terminate_route' => [
            'path' => $terminatePath,
            'trace_count' => count($terminateTraceRows),
            'trace_rows' => $terminateTraceRows,
            'final_state_code' => end($terminatePath)['current_state_code'],
        ],
        'runtime_scope_note' => 'Execution smoke currently validates rollback/branch/terminate transitions on live instances; parallel join semantics remain schema-level in this round.',
    ];
}, 'tests_bootstrap_f3cms');