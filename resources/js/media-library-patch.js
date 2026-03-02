window.initDropZone = function({ _this, rules, multiple, uploadError }) {
    const isValid = true;

    const hasDragObject = false;
    const isDropTarget = false;
    const uploadProgress = 0;

    const { ruleHelpText, fileTypeRules } = buildRuleHelpText(rules, undefined, multiple);

    function getFileTypeIsAllowed(fileType) {
        let checkType = fileType;

        if (checkType.includes('/')) {
            checkType = checkType.split('/')[1];
        }

        if (!fileTypeRules.length) {
            return true;
        }

        if (fileTypeRules.includes(checkType)) {
            return true;
        }

        if (fileTypeRules.some((acceptType) => acceptType.endsWith('*') && checkType.includes(acceptType.replace('*', '')))) {
            return true;
        }

        return false;
    }

    function handleDocumentDragenter(e) {
        e.preventDefault();
        this.hasDragObject = true;

        if (!e.dataTransfer.items || !e.dataTransfer.items.length) {
            return this.isValid = true;
        }

        if (!fileTypeRules || !fileTypeRules.length) {
            return this.isValid = true;
        }

        this.isValid = Array.from(e.dataTransfer.items).every((item) => {
            return getFileTypeIsAllowed(item.type);
        })
    }

    function handleDocumentDragleave(e) {
        if (!e.clientX && !e.clientY) {
            e.preventDefault();
            this.hasDragObject = false;
            this.isValid = true;
        }
    }

    function handleDocumentDrop(e) {
        e.preventDefault();
        this.hasDragObject = false;
        this.isValid = true;
    }

    function handleDocumentDragOver(e, element) {
        e.preventDefault();
        const overElement = element.contains(e.target);

        if (!overElement) {
            return (this.isDropTarget = false);
        }

        this.isDropTarget = true;
    }

    function handleDrop(e) {
        e.preventDefault();

        this.isValid = true;

        const files = multiple ? e.dataTransfer.files : e.dataTransfer.files[0];

        const myArguments = ['upload', files, (uploadedFilename) => {
            this.uploadCompletedSuccessfully();
        }, (error) => {
            // Error callback
            console.log('upload error', error);
        }, (event) => {
            this.uploadProgress = event.detail.progress;
        }];

        multiple ? _this.uploadMultiple(...myArguments) : _this.upload(...myArguments);
    }

    function uploadCompletedSuccessfully() {
        if (uploadError) {
            this.uploadError = uploadError;

            return;
        }
    }

    return {
        hasDragObject,
        isDropTarget,
        isValid,
        handleDrop,
        handleDocumentDragenter,
        handleDocumentDragleave,
        handleDocumentDrop,
        handleDocumentDragOver,
        uploadProgress,
        uploadCompletedSuccessfully,
        ruleHelpText,
        uploadError,
    }
}

function addToRuleHelpText(ruleHelpText, newRule) {
    if (!newRule) {
        return ruleHelpText;
    }

    return `${ruleHelpText ? ruleHelpText + ' | ' : ''}${newRule}`;
}

function buildRuleHelpText(rules = '', maxItems, multiple) {
    let fileTypeRules = [];
    let fileSizeRules = { min: '', max: '' };

    rules.toString().split('|').forEach(rule => {
        const [ruleName, ruleValue] = rule.split(':');

        if (ruleName === 'mimes') {
            fileTypeRules = ruleValue.split(',');
        }

        if (ruleName === 'max') {
            fileSizeRules.max = ruleValue;
        }

        if (ruleName === 'min') {
            fileSizeRules.min = ruleValue;
        }
    });

    let ruleHelpText = '';

    ruleHelpText = addToRuleHelpText(ruleHelpText, multiple ? 'Select or drag files' : 'Select or drag a file');

    if (fileTypeRules) {
        const amountOfRules = fileTypeRules.length;

        ruleHelpText = addToRuleHelpText(
            ruleHelpText,
            fileTypeRules.reduce((ruleHelpText, rule, i) => {
                const joiner = i === amountOfRules - 1 ? '' : ', ';

                ruleHelpText += rule.toUpperCase() + joiner;

                return ruleHelpText;
            }, '')
        );
    }

    if (fileSizeRules.min) {
        const minSizeString =
            fileSizeRules.min > 1024 ? (fileSizeRules.min / 1024).toFixed(2) + 'MB' : fileSizeRules.min + 'KB';
        ruleHelpText = addToRuleHelpText(ruleHelpText, `> ${minSizeString}`);
    }

    if (fileSizeRules.max) {
        const maxSizeString =
            fileSizeRules.max > 1024 ? (fileSizeRules.max / 1024).toFixed(2) + 'MB' : fileSizeRules.max + 'KB';
        ruleHelpText = addToRuleHelpText(ruleHelpText, `< ${maxSizeString}`);
    }

    return { ruleHelpText, fileTypeRules };
}
