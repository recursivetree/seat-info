# The syntax 
The syntax of the editor is quite similar to html, but it's not exactly the same. It only supports a small subset of 
html listed in this documentation.

## Attributes supported on all elements
* You can give every element an `ìd` property.
* Use `text-align="left"`, `text-align="center"` or `text-align="right"` to make the text left-bounded, right-bounded or
centered.

## Links
`<a href="http://example.com">Click me</a>`

`<a href="http://example.com" newtab>Click me</a>` Opens it in a new tab

`<a href="seatinfo:article/id">Click me</a>` Opens the article with id. You can find the whole url in the managment tab.

`<a href="seatinfo:resource/id">Click me</a>` Opens a resource uploaded to seat


## Links within the page
You can create links within the article to for example have a table of contents full of links that jump you to the right 
section. To create such a link, add this to jump destination: `<pagelink id="your_id">`. To actually create the link to 
the target, use a normal link like this: `<a href="#your_id">jump down</a>`.

You aren't restricted to the `pagelink` element anymore, `id` should work on any element. As of right now, it only 
remains as a compatibility element and will be removed in future versions.

## Bold
`<b>bold text</b>`


## Content separation
`<hr />` Same as the html element

The old `<hr>` syntax still works for compatibility reasons, but is deprecated.

## Newline
`<br />`

The old `<br>` syntax still works for compatibility reasons, but is deprecated.


## Titles and Subtitles
`<h1>Main title</h1>`

`<h2>Smaller title</h2>`

`<h3>Even smaller title</h3>`

`<h4>4th subtitle title</h4>`

`<h5>5th subtitle title</h5>`

`<h6>Tiny title</h6>`


## Ordered Lists
```
<ol>
    <li>list entry</li>
    <li>list entry</li>
    <li>list entry</li>
</ol>
```


## Unordered Lists
```
<ul>
    <li>list entry</li>
    <li>list entry</li>
    <li>list entry</li>
</ul>
```


## Paragraph
`<p>text</p>`

## Italic
`<i>text</i>`

## Crossed Text
`<s>crossed text</s>`

## Tables
```
<table stripes border>
    <thead>
        <tr>
            <th>Column1</th>
            <th>Column2</th>
            <th>Column3</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>1</td>
            <td>2</td>
            <td>3</td>
        </tr>
        <tr>
            <td>1</td>
            <td>2</td>
            <td>3</td>
        </tr>
        <tr>
            <td colspan="2">this thing spans two columns</td>
            <td>3</td>
        </tr>
    </tbody>
</table>
```
Add the argument `stripes` and `borders` to the table to add stripes and borders

## Images
`<img src="link-to-image.png" alt="image description" />`

You can specify a file uploaded to resources: `<img src="seatinfo:resource/id" alt="image description">`
To get the id, go to the management page. Images are put in a new paragraph. If you want the image to be inlined in the 
text, use an icon instead.

The old `<img src="...">` syntax still works for compatibility reasons, but is deprecated.

## Icons
`<icon src="link-to-image.png" alt="icon description" />`

You can specify a file uploaded to resources: `<icon src="seatinfo:resource/id" alt="icon description">`
To get the id, go to the management page. Icons are inlined in the text. If you want them to be on a new line instead, 
use an image tag.

The old `<icon src="...">` syntax still works for compatibility reasons, but is deprecated.

## Colors
```
<color color="#993399">pink</color>
```
Any css color should work.

## Audio
```
<audio src="url" />
```
Note that when playing files hosted in the info module resources, trying to change the position will change the position 
back to the start of the file. External files normally work better. This is due to Laravel(the web framework which SEAT 
bases on) not supporting certain features the browsers require.

The old `<audio src="...">` syntax still works for compatibility reasons, but is deprecated.