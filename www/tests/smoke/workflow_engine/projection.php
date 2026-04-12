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

    $draftProjection = $engine->project(['current_state_code' => 'Draft']);

    $publishResult = $engine->transit('PUBLISH', [
        'operator_id' => $operatorId,
        'current_state_code' => 'Draft',
        'operator_role_constant' => 'ROLE_PUBLISHER',
    ]);

    $publishedProjection = $engine->project([
        'current_state_code' => $publishResult['instance']['current_state_code'],
        'current_stage_codes' => $publishResult['instance']['current_stage_codes_json'],
    ]);

    $summary = [
        'draft_projection' => $draftProjection,
        'publish_result_state' => $publishResult['instance']['current_state_code'],
        'published_projection' => $publishedProjection,
        'trace_count' => count($publishResult['trace_rows']),
    ];

    if ('Draft' !== $draftProjection['current_state_code']) {
        throw new \RuntimeException('Draft projection should expose Draft state.');
    }

    if ('DRAFT' !== $draftProjection['current_stages'][0]['stage_code']) {
        throw new \RuntimeException('Draft projection should expose DRAFT stage.');
    }

    if (!in_array('PUBLISH', $draftProjection['available_action_codes'], true)) {
        throw new \RuntimeException('Draft projection should expose PUBLISH action.');
    }

    if ('PUBLISHED' !== $draftProjection['available_transitions'][0]['next_step_judgment']['next_stage_code']) {
        throw new \RuntimeException('Draft next-step judgment should point to PUBLISHED stage.');
    }

    if ('Published' !== $publishedProjection['current_state_code']) {
        throw new \RuntimeException('Published projection should expose Published state.');
    }

    if (!in_array('OFFLINE', $publishedProjection['available_action_codes'], true)) {
        throw new \RuntimeException('Published projection should expose OFFLINE action.');
    }

    if ('Offlined' !== $publishedProjection['available_transitions'][0]['next_step_judgment']['next_state_code']) {
        throw new \RuntimeException('Published next-step judgment should point to Offlined state.');
    }

    return $summary;
}, 'tests_bootstrap_f3cms');