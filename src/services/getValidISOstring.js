import { InvalidDateError } from './errors/index.js'

/**
 * Converts a given date string or Date object to a valid ISO string.
 *
 * @param { string | Date } dateString The date string or Date object to be converted.
 * @return { string } The ISO string representation of the date.
 * @throws { InvalidDateError } Throws an error if the date is invalid.
 */
export default function getValidISOstring(dateString) {
	const date = new Date(dateString)

	if (!isNaN(date.getTime())) {
		return date.toISOString()
	}

	throw new InvalidDateError('Invalid date')
}
