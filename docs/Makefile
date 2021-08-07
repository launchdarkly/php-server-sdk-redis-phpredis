
SOURCE_DIR = $(shell cd .. && pwd)
PHPDOCUMENTOR = php $(LDTOOLS_DIR)/php/phpDocumentor.phar

.PHONY: html

html:
	rm -rf build/temp build/html
	$(PHPDOCUMENTOR) \
		-d $(SOURCE_DIR)/src \
		-t build/html \
		--ignore LaunchDarkly/Impl/ \
		--visibility public \
		--defaultpackagename "SDK" \
		--title "${LD_RELEASE_DOCS_TITLE} ${LD_RELEASE_VERSION}"
