'use strict';

import React from 'react'
import PropTypes from 'prop-types'

export default function IndividualList(props) {
    const {
        individuals,
        translations,
    } = props;

    function renderIndividuals()
    {
        var count = Math.ceil(individuals.length / 3);

        var result = individuals.map((person, i) => {
            var details = '';
            if (person.birthDate !== '') {
                details += ' ' + person.birthDate;
            }
            if (person.birthLocation !== '') {
                details += ' ' + person.birthLocation;
            }
            if (person.deathDate !=='') {
                details += ' - ' + person.deathDate;
            }

            return (<p key={i}><strong><a href={person.path} target={'_blank'}>{person.name}</a></strong>{details}.</p>);
        })

        var lists = [];
        for (let i = 0; i < result.length; i += count) {
            const chuck = result.slice(i, i + count);
            lists.push(<div key={i}>{chuck}</div>);
        }
        return (<div className={'flexbox'}>{lists}</div>);
    }



    return (
        <div className={'main-container'}>
            <h3>{translations.IndividualProfiles}</h3>
            {renderIndividuals()}
        </div>
    );
}

IndividualList.propTypes = {
    individuals: PropTypes.array.isRequired,
    translations: PropTypes.object.isRequired,
};