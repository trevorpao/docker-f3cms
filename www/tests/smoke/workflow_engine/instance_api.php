<?php

require_once dirname(__DIR__, 2) . '/bootstrap/smoke.php';
require_once dirname(__DIR__, 2) . '/adapters/f3cms/bootstrap.php';

tests_smoke_run(function ($context) {
    $flowFile = $context['f3cms_dir'] . 'modules/Press/flow.json';
    $operatorId = 1;

    if (!is_file($flowFile)) {
        throw new \RuntimeException('Workflow flow.json not found: ' . $flowFile);
    }

    $workflowJson = json_decode((string) file_get_contents($flowFile), true);
    if (JSON_ERROR_NONE !== json_last_error() || !is_array($workflowJson)) {
        throw new \RuntimeException('Workflow flow.json is invalid JSON.');
    }

    $engine = new \F3CMS\WorkflowEngine($workflowJson);
    $engine->validateDefinition();

    $definition = $engine->getDefinitionPayload();

    $draftProjection = $engine->project([
        'current_state_code' => 'Draft',
    ]);

    $canPublish = $engine->canTransit('PUBLISH', [
        'current_state_code' => 'Draft',
        'operator_role_constant' => 'ROLE_PUBLISHER',
    ]);

    if (!$canPublish) {
        throw new \RuntimeException('Instance API should allow PUBLISH from Draft.');
    }

    $publishResult = $engine->transit('PUBLISH', [
        'operator_id' => $operatorId,
        'current_state_code' => 'Draft',
        'operator_role_constant' => 'ROLE_PUBLISHER',
    ]);

    $publishedProjection = $engine->project([
        'current_state_code' => $publishResult['instance']['current_state_code'],
    ]);

    if ('Draft' !== $draftProjection['current_state_code']) {
        throw new \RuntimeException('Draft projection should expose Draft state.');
    }

    if ('DRAFT' !== $draftProjection['current_stage_codes'][0]) {
        throw new \RuntimeException('Draft projection should expose DRAFT stage.');
    }

    if (!in_array('PUBLISH', $draftProjection['available_action_codes'], true)) {
        throw new \RuntimeException('Draft projection should expose PUBLISH action.');
    }

    if ('Published' !== $publishResult['instance']['current_state_code']) {
        throw new \RuntimeException('Instance API transit should move state to Published.');
    }

    if ('Published' !== $publishedProjection['current_state_code']) {
        throw new \RuntimeException('Published projection should expose Published state.');
    }

    if (!in_array('OFFLINE', $publishedProjection['available_action_codes'], true)) {
        throw new \RuntimeException('Published projection should expose OFFLINE action.');
    }

    return [
        'workflow_code' => $definition['workflow_code'],
        'workflow_version' => $definition['version'],
        'draft_projection' => $draftProjection,
        'can_publish' => $canPublish,
        'publish_result_state' => $publishResult['instance']['current_state_code'],
        'trace_count' => count($publishResult['trace_rows']),
        'published_projection' => $publishedProjection,
    ];
}, 'tests_bootstrap_f3cms');