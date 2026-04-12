<?php

require_once dirname(__DIR__, 2) . '/bootstrap/smoke.php';
require_once dirname(__DIR__, 2) . '/adapters/f3cms/bootstrap.php';

tests_smoke_run(function () {
    $workflowCode = 'PRESS_BASIC';
    $workflowVersion = 1;
    $operatorId = 1;
    $operatorRoleConstant = 'ROLE_PUBLISHER';

    $definition = \F3CMS\WorkflowEngine::loadDefinition($workflowCode, $workflowVersion);
    if (empty($definition)) {
        throw new \RuntimeException('Workflow definition not found.');
    }

    $engine = new \F3CMS\WorkflowEngine($definition);
    $engine->validateDefinition();

    $initialProjection = $engine->project(['current_state_code' => 'Draft']);

    $publishResult = $engine->transit('PUBLISH', [
        'operator_id' => $operatorId,
        'current_state_code' => 'Draft',
        'operator_role_constant' => $operatorRoleConstant,
    ]);

    $offlineResult = $engine->transit('OFFLINE', [
        'operator_id' => $operatorId,
        'current_state_code' => $publishResult['instance']['current_state_code'],
        'current_stage_codes' => $publishResult['instance']['current_stage_codes_json'],
        'trace_rows' => $publishResult['trace_rows'],
        'operator_role_constant' => $operatorRoleConstant,
    ]);

    $traceRows = $offlineResult['trace_rows'];

    return [
        'initial_state_code' => $initialProjection['current_state_code'],
        'initial_stage_codes' => $initialProjection['current_stage_codes'],
        'initial_available_action_codes' => $initialProjection['available_action_codes'],
        'publish_state_code' => $publishResult['instance']['current_state_code'],
        'publish_stage_codes' => $publishResult['instance']['current_stage_codes_json'],
        'publish_available_action_codes' => $publishResult['instance']['available_action_codes_json'],
        'offline_state_code' => $offlineResult['instance']['current_state_code'],
        'offline_stage_codes' => $offlineResult['instance']['current_stage_codes_json'],
        'offline_available_action_codes' => $offlineResult['instance']['available_action_codes_json'],
        'trace_count' => count($traceRows),
        'trace_rows' => $traceRows,
    ];
}, 'tests_bootstrap_f3cms');