import { useDropZone, useFileDialog } from '@vueuse/core'
import { ref, computed } from 'vue'

/**
 * File selection composable
 * @param {Array} options
 *
 * Special thanks to Github user adamreisnz for creating most of this file
 * https://github.com/adamreisnz
 * https://github.com/vueuse/vueuse/issues/4085
 *
 */
export function useFileSelection(options) {

	// Extract options
	const {
		dropzone,
		allowMultiple,
		allowedFileTypes,
		onFileDrop,
		onFileSelect,
	} = options

	// Data types computed ref
	const dataTypes = computed(() => {
		if (allowedFileTypes) {
			if (!Array.isArray(allowedFileTypes)) {
				return [allowedFileTypes]
			}
			return allowedFileTypes
		}
		return null
	})

	// Accept string computed ref
	const accept = computed(() => {
		if (Array.isArray(dataTypes.value)) {
			return dataTypes.value.join(',')
		}
		return '*'
	})

	// Handling of files drop
	const onDrop = files => {
		if (!files || files.length === 0) {
			return
		}
		if (files instanceof FileList) {
			files = Array.from(files)
		}
		if (files.length > 1 && !allowMultiple) {
			files = [files[0]]
		}
		if (filesList.value?.length > 0 && allowMultiple) {
			const filteredFiles = files.filter(file => !filesList.value.some(f => f.name === file.name))
			files = [...filesList.value, ...filteredFiles]
		}

		filesList.value = files
		onFileDrop && onFileDrop()
		onFileSelect && onFileSelect()
	}

	const reset = (name = null) => {
		if (name) {
			filesList.value = filesList.value.filter(file => file.name !== name).length > 0 ? filesList.value.filter(file => file.name !== name) : null
		} else {
			filesList.value = null
		}
	}

	const setFiles = (files) => {
		filesList.value = files
	}

	// Setup dropzone and file dialog composables
	const { isOverDropZone } = useDropZone(dropzone, { dataTypes: '*', onDrop })
	const { onChange, open } = useFileDialog({
		accept: accept.value,
		multiple: allowMultiple,
	})

	const filesList = ref(null)

	// Use onChange handler
	onChange(fileList => {
		onDrop(fileList)
	})

	// Expose interface
	return {
		isOverDropZone,
		openFileUpload: open,
		files: filesList,
		reset,
		setFiles,
	}
}
