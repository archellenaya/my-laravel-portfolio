export function skillGroups(expertise, skills, contract) {
    const categorized = skills.flatMap(row => {
        if (!Object.hasOwn(contract.skill_splits, row.title)) return [row];
        const split = contract.skill_splits[row.title];
        const technologies = row.description.split(',').map(name => name.trim());
        const allowed = split.technologies.map(name => name.toLowerCase());
        const belongs = name => allowed.includes(name.toLowerCase());
        const moved = technologies.filter(belongs);
        if (!moved.length) return [row];
        const remaining = technologies.filter(name => !belongs(name)).join(', ');
        return [
            {title: split.title, description: moved.join(', ')},
            ...(remaining ? [{...row, description: remaining}] : []),
        ];
    });
    let remaining = [...expertise.map(row => ({...row, description: ''})), ...categorized];
    const result = [];
    for (const [title, titles] of Object.entries(contract.skill_groups)) {
        const items = remaining.filter(row => titles.includes(row.title));
        if (items.length) {
            result.push({title, items});
            remaining = remaining.filter(row => !titles.includes(row.title));
        }
    }
    if (remaining.length) result.push({title: 'Additional Skills', items: remaining});
    return result.map(group => ({
        ...group,
        items: group.items.map(item => ({
            ...item, descriptions: item.description ? [{text: item.description}] : [],
        })),
    }));
}

export async function caseStudies(records, contract, website, asset) {
    const approved = records.filter(study => study.approved === true);
    return Promise.all(approved.map(async study => ({
        title: study.title,
        description: study.description ?? '',
        technologies: (study.technologies ?? '').split(',').map(name => name.trim()).filter(Boolean).map(name => ({name})),
        details: Object.entries(contract.case_study_fields)
            .filter(([key]) => study[key])
            .map(([key, label]) => ({label, text: study[key]})),
        links: [
            {label: 'Live website', url: website(study.live_url)},
            {label: 'Public repository', url: website(study.repository_url)},
        ].filter(link => link.url),
        screenshots: await caseScreenshot(study, asset),
    })));
}

async function caseScreenshot(study, asset) {
    if (!study.screenshot_path || !study.screenshot_alt) return [];
    if (!/^images\/(?:[\w@ -]+\/)*[\w@ .-]+\.(?:png|jpe?g|webp|gif|avif)$/i.test(study.screenshot_path)) {
        throw new Error('Invalid case study screenshot path');
    }
    return [{alt: study.screenshot_alt, url: await asset(study.screenshot_path)}];
}
