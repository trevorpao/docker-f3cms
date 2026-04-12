<?php

function tests_f3cms_load_workflow_engine($workflowCode, $workflowVersion = 1)
{
    $definition = \F3CMS\WorkflowEngine::loadDefinition($workflowCode, $workflowVersion);
    if (empty($definition)) {
        throw new \RuntimeException('Workflow definition not found.');
    }

    $engine = new \F3CMS\WorkflowEngine($definition);
    $engine->validateDefinition();

    return $engine;
}

function tests_f3cms_workflow_transition_summary($result, array $extraFields = [])
{
    $availableFields = [
        'available_action_codes' => $result['instance']['available_action_codes_json'],
        'join_pending' => $result['runtime']['join_pending'],
        'join_required_count' => $result['runtime']['join_required_count'],
        'join_completed_count' => $result['runtime']['join_completed_count'],
    ];

    $summary = [
        'action_code' => $result['transition']['action_code'],
        'transition_kind' => $result['transition']['transition_kind'],
        'current_state_code' => $result['instance']['current_state_code'],
        'current_stage_codes' => $result['instance']['current_stage_codes_json'],
    ];

    foreach ($extraFields as $field) {
        if (!array_key_exists($field, $availableFields)) {
            throw new \InvalidArgumentException('Unsupported workflow transition summary field: ' . $field);
        }

        $summary[$field] = $availableFields[$field];
    }

    return $summary;
}

function tests_f3cms_workflow_transit_runtime($engine, array &$runtimeContext, $actionCode, $operatorId, $operatorRoleConstant)
{
    $result = $engine->transit($actionCode, array_merge($runtimeContext, [
        'operator_id' => $operatorId,
        'operator_role_constant' => $operatorRoleConstant,
    ]));

    $runtimeContext = [
        'current_state_code' => $result['instance']['current_state_code'],
        'current_stage_codes' => $result['instance']['current_stage_codes_json'],
        'trace_rows' => $result['trace_rows'],
    ];

    return $result;
}