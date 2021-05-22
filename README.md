### Always use the **[latest release](https://github.com/codepotent/timeless-text/releases/latest)** on production sites! 

You've probably seen the phrases. These are common phrases you can find on a great number of websites, often on an "About Us" or bio page. How many of those sites will remember to update those numbers each year? With the Timeless Text plugin, those phrases will be automatically updated to ensure they never fall out of sync.

<blockquote>
  I have 20 years of PHP development experience.

  We have 35 years of combined experience!


  It all began 2 years ago when...
</blockquote>

---

### To Calculate Elapsed Years
To process a single date into the number of elapsed years, use the following format.

**Usage**
<code>[timeless-text y=2000 m=3 d=15 text=false]</code>

**Arguments**
* **y**
_Required_ The year value of the date to calculate.
* **m**
_Optional_ The month value of the date to calculate. Defaults to 1.
* **d**
_Optional_ The day value of the date to calculate. Defaults to 1.
* **text**
_Optional_ Set to false to suppress the text portion and only return the integer value. Defaults to true.

**Examples**

<code>I have [timeless-text y=2000 m=1 d=1] of experience with web development.</code>

<code>It all began just [timeless-text y=2000] ago.</code>

<code>So, there we were...that was [timeless-text y=2000 m=11 text=false] long years ago.</code>

---

### To Calculate Combined Years
To process a series of dates into a cumulative number of elapsed years, use the following format.

**Usage**
<code>[timeless-text combined="2000-03-14, 2010-03-14, 2000-03-14, 2010-03-16" text=false]</code>

**Arguments**
* **combined**
_Required_ The date values to calculate for cumulative years, comma-separated.
* **text**
_Optional_ Set to false to suppress the text portion and only return the integer value. Defaults to true.

**Examples**

<code>Together, we bring [timeless-text combined="2012-03-14, 2010-01-18"] of combined experience to your team.</code>


**Note**: You must provide 2 or more dates. separated by commas. Each date must include the year, month, and day. You can use hyphens or forward-slashes between those numbers.

---

[![](https://static.codepotent.com/images/logotype/code-potent-logotype-wordmark-252x36.png)](https://codepotent.com/classicpress/plugins/)
