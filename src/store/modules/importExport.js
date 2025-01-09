import { defineStore } from 'pinia'

export const useImportExportStore = defineStore(
	'importExport', {
		state: () => ({
			exportSource: '',
			exportSourceResults: '',
			exportSourceError: '',
			importedFile: null,
			importFileName: '',
		}),
		actions: {
			setExportSource(exportSource) {
				this.exportSource = exportSource
				console.info('Active exportSource set to ' + exportSource)
			},
			setImportedFile(importedFile) {
				this.importedFile = importedFile
				console.info('Active importedFile set to ' + importedFile)
			},
			setImportFileName(importFileName) {
				this.importFileName = importFileName
				console.info('Active importFileName set to ' + importFileName)
			},
			async exportFile(id, title, type) {
				const apiEndpoint = `/index.php/apps/openconnector/api/export/${type}/${id}`

				if (!id) {
					throw Error('Passed id is falsy')
				}
				const response = await fetch(
					apiEndpoint,
					{
						method: 'GET',
						headers: {
							Accept: 'application/json',
						},
					},
				)

				const blob = await response.blob()

				const download = () => {
					const url = window.URL.createObjectURL(new Blob([blob]))
					const link = document.createElement('a')
					link.href = url

					link.setAttribute('download', `${title}.json`)
					document.body.appendChild(link)
					link.click()
				}

				return { response, blob, download }
			},

		},
	},
)
