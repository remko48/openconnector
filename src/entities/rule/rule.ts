import { SafeParseReturnType, z } from 'zod'
import { TRule } from './rule.types'
import ReadonlyBaseClass from '../ReadonlyBaseClass.js'
import getValidISOstring from '../../services/getValidISOstring.js'

/**
 * Rule entity class that represents a system rule
 * Implements TRule interface and extends ReadonlyBaseClass
 */
export class Rule extends ReadonlyBaseClass implements TRule {
	public readonly id: string
	public readonly name: string
	public readonly description: string
	public readonly ruleType: string
	public readonly payload: object
	public readonly priority: number
	public readonly timeout: number
	public readonly isAsync: boolean
	public readonly allowDuplicates: boolean
	public readonly isEnabled: boolean
	public readonly oneTime: boolean
	public readonly scheduleAfter: string
	public readonly userId: string
	public readonly ruleGroupId: string
	public readonly retentionPeriod: number
	public readonly errorRetention: number
	public readonly lastTriggered: string
	public readonly nextTrigger: string
	public readonly created: string
	public readonly updated: string
	public readonly conditions: string[]
	public readonly actions: string[]
	public readonly executionCount: number
	public readonly lastExecuted: string

	/**
	 * Creates a new Rule instance
	 * @param {TRule} rule - Rule data to initialize with
	 */
	constructor(rule: TRule) {
		// Process and set default values for all rule properties
		const processedRule: TRule = {
			id: rule.id || '',
			name: rule.name || '',
			description: rule.description || '',
			ruleType: rule.ruleType || '',
			payload: rule.payload || {},
			priority: rule.priority || 1,
			timeout: rule.timeout || 3600,
			isAsync: rule.isAsync ?? true,
			allowDuplicates: rule.allowDuplicates ?? false,
			isEnabled: rule.isEnabled ?? true,
			oneTime: rule.oneTime ?? false,
			scheduleAfter: rule.scheduleAfter || '',
			userId: rule.userId || '',
			ruleGroupId: rule.ruleGroupId || '',
			retentionPeriod: rule.retentionPeriod || 3600,
			errorRetention: rule.errorRetention || 86400,
			lastTriggered: rule.lastTriggered || '',
			nextTrigger: rule.nextTrigger || '',
			created: getValidISOstring(rule.created) ?? '',
			updated: getValidISOstring(rule.updated) ?? '',
			conditions: rule.conditions || [],
			actions: rule.actions || [],
			executionCount: rule.executionCount || 0,
			lastExecuted: rule.lastExecuted || '',
		}

		super(processedRule)
	}

	/**
	 * Validates the rule object against a schema
	 * @returns {SafeParseReturnType<TRule, unknown>} Validation result
	 */
	public validate(): SafeParseReturnType<TRule, unknown> {
		const schema = z.object({
			id: z.string().uuid(),
			name: z.string().max(255),
			description: z.string().nullable(),
			ruleType: z.string(),
			payload: z.record(z.unknown()).nullable(),
			priority: z.number().int().positive(),
			timeout: z.number().int().positive(),
			isAsync: z.boolean(),
			allowDuplicates: z.boolean(),
			isEnabled: z.boolean(),
			oneTime: z.boolean(),
			scheduleAfter: z.string().nullable(),
			userId: z.string().nullable(),
			ruleGroupId: z.string().nullable(),
			retentionPeriod: z.number().int().positive(),
			errorRetention: z.number().int().positive(),
			lastTriggered: z.string().nullable(),
			nextTrigger: z.string().nullable(),
			created: z.string(),
			updated: z.string()
		})

		return schema.safeParse({ ...this })
	}
}
