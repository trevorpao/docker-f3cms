<?php

require_once dirname(__DIR__, 2) . '/bootstrap/smoke.php';
require_once dirname(__DIR__, 2) . '/adapters/f3cms/bootstrap.php';
require_once dirname(__DIR__, 2) . '/adapters/f3cms/workflow_engine_runtime.php';

tests_smoke_run(function () {
    $anyOfEngine = tests_f3cms_load_workflow_engine('PSC_CHAIN', 1);
    $anyOfPath = [];
    $anyOfRuntime = [];
    $anyOfPath[] = tests_f3cms_workflow_transition_summary(
        tests_f3cms_workflow_transit_runtime($anyOfEngine, $anyOfRuntime, 'SUBMIT', 501, 'ROLE_APPLICANT'),
        ['join_pending']
    );
    $anyOfPath[] = tests_f3cms_workflow_transition_summary(
        tests_f3cms_workflow_transit_runtime($anyOfEngine, $anyOfRuntime, 'INDIVIDUAL_PASS', 601, 'ROLE_DEPT_ADMIN'),
        ['join_pending']
    );

    $sjseEngine = tests_f3cms_load_workflow_engine('SJSE_EDGE', 1);

    $rollbackBranchPath = [];
    $rollbackRuntime = [];
    $rollbackBranchPath[] = tests_f3cms_workflow_transition_summary(
        tests_f3cms_workflow_transit_runtime($sjseEngine, $rollbackRuntime, 'SUBMIT', 101, 'ROLE_AUTHOR'),
        ['join_pending']
    );
    $rollbackBranchPath[] = tests_f3cms_workflow_transition_summary(
        tests_f3cms_workflow_transit_runtime($sjseEngine, $rollbackRuntime, 'FORMAT_PASS', 201, 'ROLE_EDITOR'),
        ['join_pending']
    );
    $rollbackBranchPath[] = tests_f3cms_workflow_transition_summary(
        tests_f3cms_workflow_transit_runtime($sjseEngine, $rollbackRuntime, 'FIRST_REVISE', 301, 'ROLE_REVIEWER'),
        ['join_pending']
    );
    $rollbackBranchPath[] = tests_f3cms_workflow_transition_summary(
        tests_f3cms_workflow_transit_runtime($sjseEngine, $rollbackRuntime, 'REVISION_SUBMIT', 101, 'ROLE_AUTHOR'),
        ['join_pending']
    );
    $rollbackBranchPath[] = tests_f3cms_workflow_transition_summary(
        tests_f3cms_workflow_transit_runtime($sjseEngine, $rollbackRuntime, 'ROUTE_TO_THIRD', 201, 'ROLE_EDITOR'),
        ['join_pending']
    );

    $parallelPath = [];
    $parallelRuntime = [];
    $parallelPath[] = tests_f3cms_workflow_transition_summary(
        tests_f3cms_workflow_transit_runtime($sjseEngine, $parallelRuntime, 'SUBMIT', 101, 'ROLE_AUTHOR'),
        ['join_pending']
    );
    $parallelPath[] = tests_f3cms_workflow_transition_summary(
        tests_f3cms_workflow_transit_runtime($sjseEngine, $parallelRuntime, 'FORMAT_PASS', 201, 'ROLE_EDITOR'),
        ['join_pending']
    );
    $parallelPath[] = tests_f3cms_workflow_transition_summary(
        tests_f3cms_workflow_transit_runtime($sjseEngine, $parallelRuntime, 'FIRST_PASS', 301, 'ROLE_REVIEWER'),
        ['join_pending']
    );
    $parallelPath[] = tests_f3cms_workflow_transition_summary(
        tests_f3cms_workflow_transit_runtime($sjseEngine, $parallelRuntime, 'FIRST_PASS', 302, 'ROLE_REVIEWER'),
        ['join_pending']
    );

    $summary = [
        'any_of' => $anyOfPath,
        'rollback_branch' => $rollbackBranchPath,
        'parallel' => $parallelPath,
    ];

    if ('INDIVIDUAL_REVIEW' !== $anyOfPath[0]['current_stage_codes'][0]) {
        throw new \RuntimeException('SUBMIT should move PSC_CHAIN to INDIVIDUAL_REVIEW.');
    }

    if ('any_of' !== \F3CMS\WorkflowEngine::loadDefinition('PSC_CHAIN')['stages'][1]['stage_type']) {
        throw new \RuntimeException('PSC_CHAIN second stage should remain any_of.');
    }

    if ('COMPREHENSIVE_REVIEW' !== $anyOfPath[1]['current_stage_codes'][0]) {
        throw new \RuntimeException('INDIVIDUAL_PASS should advance any_of stage to COMPREHENSIVE_REVIEW.');
    }

    if ('rollback' !== $rollbackBranchPath[2]['transition_kind']) {
        throw new \RuntimeException('FIRST_REVISE should execute as rollback transition.');
    }

    if ('REVISION_RESPONSE' !== $rollbackBranchPath[2]['current_stage_codes'][0]) {
        throw new \RuntimeException('Rollback path should land on REVISION_RESPONSE.');
    }

    if ('branch' !== $rollbackBranchPath[4]['transition_kind']) {
        throw new \RuntimeException('ROUTE_TO_THIRD should execute as branch transition.');
    }

    if ('THIRD_REVIEW' !== $rollbackBranchPath[4]['current_stage_codes'][0]) {
        throw new \RuntimeException('Branch path should land on THIRD_REVIEW.');
    }

    if (true !== $parallelPath[2]['join_pending']) {
        throw new \RuntimeException('First parallel reviewer should leave join pending.');
    }

    if ('FIRST_REVIEW' !== $parallelPath[2]['current_stage_codes'][0]) {
        throw new \RuntimeException('Pending parallel transition should stay on FIRST_REVIEW.');
    }

    if (false !== $parallelPath[3]['join_pending']) {
        throw new \RuntimeException('Second parallel reviewer should complete join.');
    }

    if ('EXEC_DECISION' !== $parallelPath[3]['current_stage_codes'][0]) {
        throw new \RuntimeException('Completed parallel transition should land on EXEC_DECISION.');
    }

    return $summary;
}, 'tests_bootstrap_f3cms');