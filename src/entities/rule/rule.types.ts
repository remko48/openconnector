export interface TRule {
    id: string;
    name: string;
    description: string;
    ruleType: string;
    payload?: object;
    priority: number;
    timeout?: number;
    isAsync?: boolean;
    allowDuplicates?: boolean;
    isEnabled: boolean;
    oneTime?: boolean;
    scheduleAfter?: string;
    userId: string;
    ruleGroupId: string;
    retentionPeriod?: number;
    errorRetention?: number;
    lastTriggered?: string;
    nextTrigger?: string;
    created: string;
    updated: string;
    conditions: any[];
    actions: any[];
    executionCount: number;
    lastExecuted: string;
}
