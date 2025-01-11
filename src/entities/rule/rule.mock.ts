import { Rule } from './rule'
import { TRule } from './rule.types'

/**
 * Mock rule data for testing purposes
 * @returns {TRule[]} Array of mock rule data
 */
export const mockRuleData = (): TRule[] => [
	{
		id: '5137a1e5-b54d-43ad-abd1-4b5bff5fcd3f',
		name: 'System Backup Rule',
		description: 'Rule for system backup process',
		ruleType: 'system.backup',
		priority: 1,
		isEnabled: true,
		userId: 'admin',
		ruleGroupId: 'system-rules',
		created: '',
		updated: '',
		conditions: [],
		actions: [],
		executionCount: 0,
		lastExecuted: '',
	},
	{
		id: '4c3edd34-a90d-4d2a-8894-adb5836ecde8',
		name: 'Weekly Report Rule',
		description: 'Rule for generating weekly reports',
		ruleType: 'system.report',
		priority: 2,
		isEnabled: true,
		userId: 'reporter',
		ruleGroupId: 'reporting-rules',
		created: '',
		updated: '',
		conditions: [],
		actions: [],
		executionCount: 0,
		lastExecuted: '',
	},
]

/**
 * Creates Rule instances from mock data
 * @param {TRule[]} data - Optional mock rule data to use
 * @returns {Rule[]} Array of Rule instances
 */
export const mockRule = (data: TRule[] = mockRuleData()): Rule[] => data.map(item => new Rule(item))
