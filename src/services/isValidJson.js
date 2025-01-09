/**
 * Validates if a given string is a valid JSON.
 *
 * @param {string} jsonString - The string to be validated.
 * @return {boolean} Returns true if the string is a valid JSON, false otherwise.
 */
export default function isValidJson(jsonString) {
	try {
		JSON.parse(jsonString)
		return true
	} catch (e) {
		return false
	}
}
