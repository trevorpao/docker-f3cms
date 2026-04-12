<?php

require_once dirname(__DIR__, 2) . '/bootstrap/smoke.php';
require_once dirname(__DIR__, 2) . '/adapters/f3cms/bootstrap.php';

tests_smoke_run(function () {
    $definition = \F3CMS\WorkflowEngine::loadDefinition('PSC_CHAIN');

    if (empty($definition)) {
        throw new \RuntimeException('Workflow definition not found: PSC_CHAIN');
    }

    $roleConstants = array_map(function ($row) {
        return $row['role_constant'];
    }, $definition['role_map']);

    $transitionMap = [];
    foreach ($definition['transitions'] as $transition) {
        $transitionMap[$transition['transition_code']] = [
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
        'role_constants' => $roleConstants,
        'linear_path' => [
            'PSC_SUBMIT' => $transitionMap['PSC_SUBMIT'],
            'PSC_INDIVIDUAL_PASS' => $transitionMap['PSC_INDIVIDUAL_PASS'],
            'PSC_COMPREHENSIVE_PASS' => $transitionMap['PSC_COMPREHENSIVE_PASS'],
            'PSC_CHAIR_PASS' => $transitionMap['PSC_CHAIR_PASS'],
            'PSC_COURSE_PASS' => $transitionMap['PSC_COURSE_PASS'],
        ],
        'reject_path' => [
            'PSC_INDIVIDUAL_REJECT' => $transitionMap['PSC_INDIVIDUAL_REJECT'],
            'PSC_COMPREHENSIVE_REJECT' => $transitionMap['PSC_COMPREHENSIVE_REJECT'],
            'PSC_CHAIR_REJECT' => $transitionMap['PSC_CHAIR_REJECT'],
            'PSC_COURSE_REJECT' => $transitionMap['PSC_COURSE_REJECT'],
        ],
    ];

    if ('LOCK' !== $definition['entry_stage_code']) {
        throw new \RuntimeException('PSC_CHAIN entry stage should be LOCK.');
    }

    if ('forward' !== $transitionMap['PSC_COURSE_PASS']['transition_kind']) {
        throw new \RuntimeException('PSC_COURSE_PASS should be forward transition.');
    }

    if ('CERTIFICATE_PICKUP' !== $transitionMap['PSC_COURSE_PASS']['to_stage_code']) {
        throw new \RuntimeException('PSC_COURSE_PASS should lead to CERTIFICATE_PICKUP.');
    }

    if ('terminate' !== $transitionMap['PSC_CHAIR_REJECT']['transition_kind']) {
        throw new \RuntimeException('PSC_CHAIR_REJECT should be terminate transition.');
    }

    if ('REJECTED' !== $transitionMap['PSC_CHAIR_REJECT']['to_stage_code']) {
        throw new \RuntimeException('PSC_CHAIR_REJECT should lead to REJECTED.');
    }

    if (!in_array('ROLE_DEPT_CHAIR', $roleConstants, true)) {
        throw new \RuntimeException('ROLE_DEPT_CHAIR should exist in PSC_CHAIN role map.');
    }

    return $summary;
}, 'tests_bootstrap_f3cms');