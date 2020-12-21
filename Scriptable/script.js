const widget = await createWidget()

if (!config.runsInWidget)
{
    await widget.presentMedium()
}

Script.setWidget(widget)
Script.complete()

async function createWidget(items)
{
    switch(Device.model()) {
        case 'iPad':
            stackWidth = 148;
            break;
        case 'iPhone':
            stackWidth = 138;
            break;
    }

    /**
     * Read JSON data
     * @type {Request}
     */
    let r = new Request('WRITE_YOUR_URL')
    let json = await r.loadJSON()

    /**
     * Init Widget
     * @type {ListWidget}
     */
    let widget = new ListWidget()
    widget.backgroundColor = Color.dynamic(Color.white(), new Color('2c2c2e'))

    /**
     * Add to Widget main data
     */
    let trimValue = widget.addText(json.trimestre.value)
    trimValue.font = Font.title2()
    trimValue.centerAlignText()

    widget.addSpacer(5)

    let trimLegend = widget.addText('utile ' + json.trimestre.periodo + '° trimestre')
    trimLegend.font = Font.systemFont(12)
    trimLegend.centerAlignText()

    widget.addSpacer(15)

    /**
     * Add left Stack into Widget
     */
    let stackContainer = widget.addStack()

    let leftContainer = stackContainer.addStack()
    leftContainer.size = new Size(stackWidth, 0)
    leftContainer.cornerRadius = 10
    leftContainer.backgroundColor = Color.green()
    leftContainer.setPadding(10, 10, 10, 10)
    leftContainer.layoutVertically()

    /**
     * Add Text into left Stack
     */
    let monthUpValue = leftContainer.addText(json.entrate)
    monthUpValue.font = Font.boldSystemFont(16)
    monthUpValue.textColor = Color.white()

    leftContainer.addSpacer(5)

    let monthUpLegend = leftContainer.addText('entrate')
    monthUpLegend.font = Font.systemFont(12)
    monthUpLegend.textColor = Color.white()

    stackContainer.addSpacer(15)

    /**
     * Add right Stack into Widget
     */
    let rightContainer = stackContainer.addStack()
    rightContainer.size = new Size(stackWidth, 0)
    rightContainer.cornerRadius = 10
    rightContainer.backgroundColor = Color.red()
    rightContainer.setPadding(10, 10, 10, 10)
    rightContainer.layoutVertically()

    /**
     * Add Text into right Stack
     */
    let monthDownValue = rightContainer.addText(json.uscite)
    monthDownValue.font = Font.boldSystemFont(16)
    monthDownValue.textColor = Color.white()

    rightContainer.addSpacer(5)

    let monthDownLegend = rightContainer.addText('uscite')
    monthDownLegend.font = Font.systemFont(12)
    monthDownLegend.textColor = Color.white()

    /**
     * Set refresh
     * @type {number}
     */
    let interval = 1000 * 60 * 60 * 1
    widget.refreshAfterDate = new Date(Date.now() + interval)

    return widget
}
