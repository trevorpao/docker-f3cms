<?php

require_once dirname(__DIR__, 2) . '/bootstrap/smoke.php';
require_once dirname(__DIR__, 2) . '/adapters/f3cms/bootstrap.php';

tests_smoke_run(function () {
    $definition = \F3CMS\WorkflowEngine::loadDefinition('SJSE_EDGE');

    if (empty($definition)) {
        throw new \RuntimeException('Workflow definition not found: SJSE_EDGE');
    }

    $stageTypeMap = [];
    foreach ($definition['stages'] as $stage) {
        $stageTypeMap[$stage['stage_code']] = [
            'stage_type' => $stage['stage_type'],
            'join_policy_type' => $stage['join_policy_type'],
            'meta_json' => $stage['meta_json'],
        ];
    }

    $transitionKinds = [];
    $actionMap = [];
    foreach ($definition['transitions'] as $transition) {
        $transitionKinds[$transition['transition_kind']] = true;
        $actionMap[$transition['transition_code']] = [
            'from_stage_code' => $transition['from_stage_code'],
            'to_stage_code' => $transition['to_stage_code'],
            'to_state_code' => $transition['to_state_code'],
            'transition_kind' => $transition['transition_kind'],
        ];
    }

    $summary = [
        'workflow_code' => $definition['workflow_code'],
        'entry_stage_code' => $definition['entry_stage_code'],
        'stage_count' => count($definition['stages']),
        'transition_count' => count($definition['transitions']),
        'role_map_count' => count($definition['role_map']),
        'transition_kinds' => array_values(array_keys($transitionKinds)),
        'first_review_stage' => $stageTypeMap['FIRST_REVIEW'],
        'route_selection_stage' => $stageTypeMap['ROUTE_SELECTION'],
        'revision_transition' => $actionMap['SJSE_FIRST_REVISE'],
        'third_review_transition' => $actionMap['SJSE_FIRST_TO_THIRD'],
        'withdraw_transition' => $actionMap['SJSE_WITHDRAW'],
    ];

    if ('parallel' !== $stageTypeMap['FIRST_REVIEW']['stage_type']) {
        throw new \RuntimeException('FIRST_REVIEW stage_type should be parallel.');
    }

    if ('all_of' !== $stageTypeMap['FIRST_REVIEW']['join_policy_type']) {
        throw new \RuntimeException('FIRST_REVIEW join_policy_type should be all_of.');
    }

    if ('branch' !== $stageTypeMap['ROUTE_SELECTION']['stage_type']) {
        throw new \RuntimeException('ROUTE_SELECTION stage_type should be branch.');
    }

    if ('rollback' !== $actionMap['SJSE_FIRST_REVISE']['transition_kind']) {
        throw new \RuntimeException('SJSE_FIRST_REVISE should be rollback transition.');
    }

    if ('branch' !== $actionMap['SJSE_FIRST_TO_THIRD']['transition_kind']) {
        throw new \RuntimeException('SJSE_FIRST_TO_THIRD should be branch transition.');
    }

    if ('terminate' !== $actionMap['SJSE_WITHDRAW']['transition_kind']) {
        throw new \RuntimeException('SJSE_WITHDRAW should be terminate transition.');
    }

    return $summary;
}, 'tests_bootstrap_f3cms');