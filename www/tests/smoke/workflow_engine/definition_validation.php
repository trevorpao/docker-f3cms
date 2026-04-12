<?php

require_once dirname(__DIR__, 2) . '/bootstrap/smoke.php';
require_once dirname(__DIR__, 2) . '/adapters/f3cms/bootstrap.php';

tests_smoke_run(function () {
    $definition = \F3CMS\WorkflowEngine::loadDefinition('SJSE_EDGE');

    $invalidDefinition = $definition;
    $invalidDefinition['entry_stage_code'] = 'NOT_FOUND_STAGE';
    $invalidDefinition['definition_json']['workflow']['entryStageCode'] = 'NOT_FOUND_STAGE';

    $invalidMessage = null;
    try {
        \F3CMS\WorkflowEngine::validateDefinitionPayload($invalidDefinition);
    } catch (\Throwable $e) {
        $invalidMessage = $e->getMessage();
    }

    if (null === $invalidMessage || false === strpos($invalidMessage, 'entry_stage_code not found')) {
        throw new \RuntimeException('Definition validator should block invalid entry_stage_code.');
    }

    return [
        'workflow_code' => $definition['workflow_code'],
        'version' => $definition['version'],
        'stage_count' => count($definition['stages']),
        'transition_count' => count($definition['transitions']),
        'entry_stage_code' => $definition['entry_stage_code'],
        'validation' => [
            'valid_definition' => true,
            'invalid_definition_message' => $invalidMessage,
        ],
    ];
}, 'tests_bootstrap_f3cms');