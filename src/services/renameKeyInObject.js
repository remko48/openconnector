/**
 * Renames a key in an object. Only works for one level deep.
 *
 * @param {object} obj - The object containing the key to be renamed.
 * @param {string} oldKey - The key in the object to be renamed.
 * @param {string} newKey - The new key name.
 * @return {object} A new object with the key renamed.
 * @throws an error if the obj, oldKey, or newKey is not provided
 */
export default function renameKey(obj, oldKey, newKey) {
	if (!obj) {
		console.error('Invalid argument: obj is', obj)
		throw new Error('Invalid argument: obj is required')
	}
	if (typeof obj !== 'object' || Array.isArray(obj)) {
		console.error('Invalid argument: obj is not a valid JSON object', obj)
		throw new Error('Invalid argument: obj is not a valid JSON object')
	}
	if (!oldKey) {
		console.error('Invalid argument: oldKey is', oldKey)
		throw new Error('Invalid argument: oldKey is required')
	}
	if (!newKey) {
		console.error('Invalid argument: newKey is', newKey)
		throw new Error('Invalid argument: newKey is required')
	}

	const keys = Object.keys(obj)
	const newObj = {}

	for (const key of keys) {
		if (key === oldKey) {
			newObj[newKey] = obj[oldKey]
		} else {
			newObj[key] = obj[key]
		}
	}

	return newObj
}
