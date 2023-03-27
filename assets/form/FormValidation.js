export default function FormValidation(element) {
    element.errors = []
    element.constraints.map(constraint => {
        switch (constraint.class){
            case 'NotBlank':
                element = notBlankValidator(element, constraint)
                break;
            case 'Length':
                element = lengthValidator(element, constraint)
                break;
            case 'Choice':
                element = choiceValidator(element, constraint)
                break;
            case 'Colour':
                element = colourValidator(element, constraint)
                break;
            default:
                console.log(element,constraint)

        }
    })
    return element
}

function notBlankValidator(element, constraint) {
    if (element.block_prefixes.includes('time'))
    {
        let error = false
        element.children.map(child => {
            if (typeof child.value !== 'string') {
                if (! error) {
                    error = true
                    element.errors.push(constraint.message)
                }
            }
        })
        return element
    }
    const str = element.value
    if (!str || /^\s*$/.test(str))
        element.errors.push(constraint.message)
    return element
}

function lengthValidator(element, constraint) {
    const str = element.value
    if (!str || /^\s*$/.test(str))
        return element

    if (constraint.min !== null){
        if (str.length < constraint.min)
            element.errors.push(constraint.minMessage)
    }
    if (constraint.max !== null){
        if (str.length > constraint.max) {
            element.errors.push(constraint.maxMessage)
        }
    }
    if (constraint.max !== null && constraint.max === constraint.min){
        if (str.length !== constraint.max) {
            element.errors.push(constraint.exactMessage)
        }
    }
    //  charset testing is not implemented here, but the server will catch it.
    return element
}

function choiceValidator(element, constraint) {
    let str = element.value
    if (!str || /^\s*$/.test(str))
        return element

    if (constraint.multiple === true && constraint.min !== null){
        if (str.length < constraint.min)
            element.errors.push(constraint.minMessage)
    }

    if (constraint.multiple === true && constraint.max !== null){
        if (str.length > constraint.max) {
            element.errors.push(constraint.maxMessage)
        }
    }

    if (constraint.multiple === false && ! constraint.choices.includes(str))
        element.errors.push(constraint.message)

    if (constraint.multiple === true) {
        let ok = true
        str.map((value,key) => {
            if (! constraint.choices.includes(value))
                ok = false
        })
        if (! ok)
            element.errors.push(constraint.multipleMessage)
    }
    return element
}

function colourValidator(element, constraint) {
    let str = element.value
    if (!str || /^\s*$/.test(str))
        return element

    if (!/^#[0-9A-F]{6}$/i.test(str))
        element.errors.push(constraint.message)
    return element
}
