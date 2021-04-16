/**
 * based on https://www.npmjs.com/package/node-bbcode
 */
"use strict";
const VERSION = '1.0.0'
var options = { classPrefix: 'bbcode', newLine: false, allowData: false, allowClasses: false }

const parseAttributes = (tag, attrs) =>
{
  let obj = {attr: {}, class: [], data: {}}
  if (!attrs) return obj

  attrs = attrs.match(/[a-zA-Z0-9_-]+=(\'|").*?(\'|")/g)
  if (attrs)
  {
    for (var i = 0; i < attrs.length; i++)
    {
      let tmp = attrs[i]
      tmp = tmp.trim().split('=').map((k) => { return k.replace(/(^(\'|")+|(\'|")+$)/mg, '') })
      if (tmp[0] === 'class')
      {
        obj.class.push(tmp[1])
      }
      else
      {
        let type = (tmp[0].includes('data-')) ? 'data' : 'attr'
        obj[type][tmp[0]] = tmp[1]
      }
    }
  }

  return obj
}

const parseDataAttrs = (dataList) =>
{
  let dataTags = ''
  Object.keys(dataList).forEach((b) => { dataTags += ' '+b+'="'+dataList[b]+'" ' })
  return dataTags
}
const getUrl = (linkObj) =>
{
    return linkObj.attr.wurl !== undefined? 'admin.php?page='+linkObj.attr.wurl:linkObj.attr.url
}
const parseTag = (string, tag, attrs, value) =>
{
  tag = tag.toLowerCase()
  let val = ''
  let parseAttr = parseAttributes(tag, /\[(.*?)\]/g.exec(string)[1])
  let tagDetails = {
    attr: parseAttr.attr,
    data: (options.allowData) ? parseDataAttrs(parseAttr.data) : '',
    class: (options.allowClasses) ? options.classPrefix+' '+parseAttr.class.join(' ')+' ' : options.classPrefix+' ',
  }

  switch (tag)
  {
    case 'url':
      val = '<a class="' + tagDetails.class + 'link" '+ tagDetails.data
      if (tagDetails.attr.alt) val += ' alt="'+tagDetails.attr.alt+'"'
      return val + ' href="' + getUrl(tagDetails) + '">' + value + '</a>'
    case 'b':
      return '<strong '+ tagDetails.data +' >' + value + '</strong>'
    case 'i':
      return '<em '+ tagDetails.data +' >' + value + '</em>'
    case 'u':
      return '<span style="text-decoration:underline" '+ tagDetails.data +' >' + value + '</span>'
    case 's':
      return '<span style="text-decoration:line-through" '+ tagDetails.data +' >' + value + '</span>'
    case 'list':
    case 'ol':
    case 'ul':
      if (tag === 'list') tag = 'ul'
      val = '<' + tag + ' ' + tagDetails.data + ' class="' + tagDetails.class + '">'
      val += value.replace(new RegExp('\\[li]((?:.|[\r\n])*?)\\[/li]', 'ig'), (string, value) => { return '<li>'+value.trim()+'</li>' })
      return val + '</' + tag + '>'
    case 'span':
    case 'h1':
    case 'h2':
    case 'h3':
    case 'h4':
    case 'h5':
    case 'h6':
      return '<' + tag + tagDetails.data +' class="' + tagDetails.class +' '+ tag+'">' + value + '</' + tag + '>'
    case 'img':
      val = '<img class="' + tagDetails.class + 'image" src="' + value +' '+ tagDetails.data +'"'
      if (tagDetails.attr.width) val += ' width="'+tagDetails.attr.width+'px"'
      if (tagDetails.attr.height) val += ' height="'+tagDetails.attr.height+'px"'
      if (tagDetails.attr.title) val += ' title="'+tagDetails.attr.title+'"'
      if (tagDetails.attr.alt) val += ' alt="'+tagDetails.attr.alt+'"'
      return val + '>'
  }

  return string
}

const bbcode = module.exports =
{
  render: (content, newOptions) =>
  {
    let regex = new RegExp('\\[(\\w+)(?:[= ]([^\\]]+))?]((?:.|[\r\n])*?)\\[/\\1]', 'ig')

    options = Object.assign(options, newOptions)
    if (newOptions.newLine) content = content.replace(/\r?\n/g, '<br>')
    return content.replace(regex, parseTag.bind())
  },

  version: VERSION,
}
