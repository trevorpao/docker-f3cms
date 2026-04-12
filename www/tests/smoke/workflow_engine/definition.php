<?php

require_once dirname(__DIR__, 2) . '/bootstrap/smoke.php';
require_once dirname(__DIR__, 2) . '/adapters/f3cms/bootstrap.php';

$workflowCode = isset($argv[1]) ? trim((string) $argv[1]) : 'PRESS_BASIC';

tests_smoke_run(function () use ($workflowCode) {
    $definition = \F3CMS\WorkflowEngine::loadDefinition($workflowCode);

    if (empty($definition)) {
        fwrite(STDERR, 'Workflow definition not found: ' . $workflowCode . PHP_EOL);
        exit(2);
    }

    return [
        'workflow_code' => $definition['workflow_code'],
        'version' => $definition['version'],
        'status' => $definition['status'],
        'entry_stage_code' => $definition['entry_stage_code'],
        'stage_count' => count($definition['stages']),
        'transition_count' => count($definition['transitions']),
        'role_map_count' => count($definition['role_map']),
        'stages' => array_map(function ($row) {
            return [
                'stage_code' => $row['stage_code'],
                'stage_type' => $row['stage_type'],
            ];
        }, $definition['stages']),
        'transitions' => array_map(function ($row) {
            return [
                'transition_code' => $row['transition_code'],
                'from_stage_code' => $row['from_stage_code'],
                'action_code' => $row['action_code'],
                'to_stage_code' => $row['to_stage_code'],
                'to_state_code' => $row['to_state_code'],
            ];
        }, $definition['transitions']),
        'role_map' => array_map(function ($row) {
            return [
                'role_constant' => $row['role_constant'],
                'role_label' => $row['role_label'],
            ];
        }, $definition['role_map']),
    ];
}, 'tests_bootstrap_f3cms');