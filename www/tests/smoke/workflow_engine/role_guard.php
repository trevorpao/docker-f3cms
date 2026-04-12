<?php

require_once dirname(__DIR__, 2) . '/bootstrap/smoke.php';
require_once dirname(__DIR__, 2) . '/adapters/f3cms/bootstrap.php';

tests_smoke_run(function () {
    $workflowCode = 'PRESS_BASIC';
    $workflowVersion = 1;
    $operatorId = 1;

    $definition = \F3CMS\WorkflowEngine::loadDefinition($workflowCode, $workflowVersion);
    if (empty($definition)) {
        throw new \RuntimeException('Workflow definition not found.');
    }

    $engine = new \F3CMS\WorkflowEngine($definition);
    $engine->validateDefinition();

    $publishResult = $engine->transit('PUBLISH', [
        'operator_id' => $operatorId,
        'current_state_code' => 'Draft',
        'operator_role_constant' => 'ROLE_PUBLISHER',
    ]);

    $blockedMessage = null;

    try {
        $engine->transit('OFFLINE', [
            'operator_id' => $operatorId,
            'current_state_code' => $publishResult['instance']['current_state_code'],
            'current_stage_codes' => $publishResult['instance']['current_stage_codes_json'],
            'trace_rows' => $publishResult['trace_rows'],
            'operator_role_constant' => 'ROLE_EDITOR',
        ]);
    } catch (\Throwable $e) {
        $blockedMessage = $e->getMessage();
    }

    if ('Workflow operator role not allowed for current stage.' !== $blockedMessage) {
        throw new \RuntimeException('Illegal role was not blocked as expected.');
    }

    $traceRows = $publishResult['trace_rows'];
    $currentInstance = $publishResult['instance'];

    return [
        'publish_state_code' => $publishResult['instance']['current_state_code'],
        'blocked_message' => $blockedMessage,
        'trace_count' => count($traceRows),
        'trace_rows' => $traceRows,
        'current_state_code' => $currentInstance['current_state_code'],
        'current_stage_codes' => $currentInstance['current_stage_codes_json'],
    ];
}, 'tests_bootstrap_f3cms');