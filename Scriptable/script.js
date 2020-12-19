const widget = await createWidget()

if (!config.runsInWidget)
{
    await widget.presentMedium()
}

Script.setWidget(widget)
Script.complete()

async function createWidget(items)
{
    let r = new Request('WRITE_YOUR_URL')
    let json = await r.loadJSON()

    let widget = new ListWidget()

    let trimValue = widget.addText(json.trimestre.value)
    trimValue.font = Font.title2()
    trimValue.centerAlignText()

    widget.addSpacer(5)

    let trimLegend = widget.addText('utile ' + json.trimestre.periodo + '° trimestre')
    trimLegend.font = Font.systemFont(12)
    trimLegend.centerAlignText()

    widget.addSpacer(15)

    let stackContainer = widget.addStack()

    let leftContainer = stackContainer.addStack()
    leftContainer.size = new Size(138, 0)
    leftContainer.cornerRadius = 10
    leftContainer.backgroundColor = Color.green()
    leftContainer.setPadding(10, 10, 10, 10)
    leftContainer.layoutVertically()

    let monthUpValue = leftContainer.addText(json.entrate)
    monthUpValue.font = Font.boldSystemFont(16)

    leftContainer.addSpacer(5)

    let monthUpLegend = leftContainer.addText('entrate')
    monthUpLegend.font = Font.systemFont(12)

    stackContainer.addSpacer(15)

    let rightContainer = stackContainer.addStack()
    rightContainer.size = new Size(138, 0)
    rightContainer.cornerRadius = 10
    rightContainer.backgroundColor = Color.red()
    rightContainer.setPadding(10, 10, 10, 10)
    rightContainer.layoutVertically()

    let monthDownValue = rightContainer.addText(json.uscite)
    monthDownValue.font = Font.boldSystemFont(16)

    rightContainer.addSpacer(5)

    let monthDownLegend = rightContainer.addText('uscite')
    monthDownLegend.font = Font.systemFont(12)

    let interval = 1000 * 60 * 60 * 1
    widget.refreshAfterDate = new Date(Date.now() + interval)

    return widget
}
